<?php
//Functions
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function set_if_exists($s){
    $r = "";
    if(isset($s)){
        $r = $s;
    }
    return $r;
}

// Open Preference Extract
$extract = fopen("data/preference_extract.csv", "r");

//Parse Data
$extractData = array(); //Data array

while (($row = fgetcsv($extract)) !== false) {
    array_push($extractData, $row);
}
fclose($extract);

// remove CSV table headers
array_shift($extractData);

// get job data
$presentData = array();
$dataCount = 1;
foreach ($extractData as $i => $val) {
    $a = array();
    $location = $val[1];
    $a['id'] = $i;
    $a['rank'] = $dataCount; //assign rank
    $a['name'] = $val[1];
    if (str_contains($location, "Mersey")) {
        $a['im1'] = get_string_between($val[10], "IM1", "IM2");
        $a['im2'] = get_string_between($val[10], "IM2", "IM3");
        $a['im3'] = get_string_between($val[10], "IM3", "All posts");
        $a['deanery'] = "Merseyside";
        
    } elseif (str_contains($location, "Manchester")) {
        $a['im1'] = get_string_between($val[10], "IM1 -", "IM2");
        $a['im2'] = get_string_between($val[10], "IM2 -", "IM3");
        $a['im3'] = get_string_between($val[10], "IM3 -", "All track");
        $a['deanery'] = "Manchester";
    } else {
        $a['im1'] = "";
        $a['im2'] = "";
        $a['im3'] = "";
        $a['deanery'] = "Unspecified";
    }
    array_push($presentData, $a);
    //

    $dataCount++;
}

//sort the array based on rank
usort($presentData, function ($a, $b){
    return $a['rank'] <=> $b['rank'];
});

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.js" integrity="sha256-nQLuAZGRRcILA+6dMBOvcRh5Pe310sBpanc6+QBmyVM=" crossorigin="anonymous"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oriel Ranking System</title>
</head>
<body>
    <h1>Ranking Tool</h1>
    <div>
        <p>Total number of jobs: <?php echo count($presentData); ?></p>
    </div>
    <div class="row">
        <div class="col-12 p-3">
            <table id="rank-table" class="table small">
                <tr class="strong">
                    <th>Options</th>
                    <th>Rank</th>
                    <th>Deanery</th>
                    <th>IMT 1</th>
                    <th>IMT 2</th>
                    <th>IMT 3</th>
                    <th>Name</th>
                </tr>
                <?php
                    foreach ($presentData as $i => $val) {
                        $im1Val = set_if_exists($val['im1']);
                        $im2Val = set_if_exists($val['im2']);
                        $im3Val = set_if_exists($val['im3']);
                        $rank = set_if_exists($val['rank']);
                        $id = set_if_exists($val['id']);
                        $deanery = set_if_exists($val['deanery']);
                        $name = set_if_exists($val['name']);
                        $tr = <<<HTML
                        <tr>
                            <td><div></div></td>
                            <td contenteditable="true" class="job-rank" data-id="$id">$rank</td>
                            <td>$deanery</td>
                            <td>$im1Val</td>
                            <td>$im2Val</td>
                            <td>$im3Val</td>
                            <td>$name<td>
                        </tr>
                        HTML;
                        if(strlen($deanery)){
                            echo $tr;
                        }
                    }
                ?>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            var chosenRank = 0; 

            // Get current order
            let order = []
            $(".job-rank").each(function(){
                let pair = [$(this).data("id"), $(this).text()]
                order.push(pair);
            });
            
            function reorder(){
                let rows = $("table tr").get();
                rows.sort(function(a, b) {
                    var valueA = parseFloat($(a).find('td:eq(1)').text()); // Get the number from the second column
                    var valueB = parseFloat($(b).find('td:eq(1)').text()); // Get the number from the second column

                    // Compare the values numerically (ascending order)
                    return valueA - valueB;
                });
                $.each(rows, function(index, row) {
                    $('table').append(row); // Append each row back into the table in the new order
                });
            }

            $(".job-rank").on("focus", function(){
                chosenRank = parseInt($(this).text());
            });

            $(".job-rank").on("blur", function(){
                let newVal = parseInt($(this).text());
                if (newVal !== chosenRank) {
                   let id = $(this).data("id");
                    $(".job-rank").each(function(){
                        let compare = parseInt($(this).text());
                        if(compare < chosenRank && compare > newVal){
                            //Rank increased
                            compare = compare +1;   
                            $(this).text(compare);
                        } else if (compare > chosenRank && compare < newVal) {
                            //Rank decreased
                            compare = compare -1;
                            $(this).text(compare);
                        } else if (newVal - compare == 0) {
                            //Handle duplicate
                            compareID = $(this).data("id");
                            if(compareID !== id){
                                compare = compare +1;
                                $(this).text(compare);
                            }
                        }
                    });
                    reorder(); 
                }
            });
        });
    </script>
</body>
</html>