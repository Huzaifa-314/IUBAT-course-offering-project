<?php

	$db = mysqli_connect('localhost', 'root', '', 'iubat');
	if($db){
		 //echo 'database connection established!';
	}else{
		echo 'database connection error!';
	}


    
    //filling schedule function
    function fillSchedule($selectedDayTime){
        global $schedule,$time,$day;
        // getting day and time corresponding to a value
        for($i = 0; $i < sizeof($time); $i++){
            ${$time[$i]['Time']}=$i;
        }
        for($i = 0; $i < sizeof($day); $i++){
            ${$day[$i]['Day']}=$i;
        }


        $eff=0;
        // filling up schedule for not conflict course
        for($j=0; $j<sizeof($selectedDayTime); $j++){
            $schedule[${$selectedDayTime[$j]['Day']}][${$selectedDayTime[$j]['Time']}] += 1;
            $eff += ${$selectedDayTime[$j]['Time']};
        }

        return $eff;
    }

    // getting all courses name
    $courses = mysqli_fetch_all(mysqli_query($db, "SELECT DISTINCT Course FROM fulltable ORDER BY fulltable.Course ASC"),MYSQLI_ASSOC);
    
    // filtering main and lab courses
    $labCourseIndex = [1,5];
    $mainCourseIndex = [0,2,3,4];
    $labCourses = array();
    $mainCourses = array();
    for($i = 0; $i < sizeof($labCourseIndex); $i++){
        array_push($labCourses, strtolower($courses[$labCourseIndex[$i]]['Course']));
    }
    for($i = 0; $i < sizeof($mainCourseIndex); $i++){
        array_push($mainCourses, strtolower($courses[$mainCourseIndex[$i]]['Course']));
    }
    
    // creating table of all course
    for($i = 0; $i < sizeof($courses); $i++){
        $cName=strtolower($courses[$i]['Course']);
        mysqli_query($db, "CREATE TABLE IF NOT EXISTS `$cName` AS SELECT * FROM fulltable WHERE Course = '$cName'");
    }

    //getting information from all course table (variable example:  ${'csc 283'})
    for($i = 0; $i < sizeof($courses); $i++){
        $cName=strtolower($courses[$i]['Course']);
        ${$cName} = mysqli_fetch_all (mysqli_query($db,"SELECT * FROM `$cName`"), MYSQLI_ASSOC);
    }


    // getting unique day and time
    $time = mysqli_fetch_all(mysqli_query($db, "SELECT DISTINCT Time FROM fulltable"),MYSQLI_ASSOC);
    $day = mysqli_fetch_all(mysqli_query($db, "SELECT DISTINCT Day FROM fulltable"),MYSQLI_ASSOC);

    // total section of each course (variable example:  ${'Scsc 283'}   )
    for($i = 0; $i < sizeof($mainCourses); $i++){
        $McName = $mainCourses[$i];
        ${'S'.$McName} = mysqli_fetch_all(mysqli_query($db, "SELECT DISTINCT Section FROM `$McName` ORDER BY `$McName`.`Section` ASC"),MYSQLI_ASSOC);
    }

    //permutation begin
    $c=1; //combination counter. Total combination: ($c-1)
    // condition:   sizeof(${'S'.$mainCourses[0]})
    for($i = 0; $i <sizeof(${'S'.$mainCourses[0]}); $i++){
        for($j = 0; $j < sizeof(${'S'.$mainCourses[1]}); $j++){
            for($k = 0; $k < sizeof(${'S'.$mainCourses[2]}); $k++){
                for($l = 0; $l < sizeof(${'S'.$mainCourses[3]}); $l++){
                    
                    ${'combo'.$c++} = ${'S'.$mainCourses[0]}[$i]['Section'].${'S'.$mainCourses[1]}[$j]['Section'].${'S'.$mainCourses[2]}[$k]['Section'].${'S'.$mainCourses[3]}[$l]['Section'].'<br>';
                }
            }
        }
    }

    $selectedCourse = $mainCourses[0];
    $selectedLCourse = $labCourses[0];
    $selectedCourse1 = $mainCourses[1];
    $selectedCourse2 = $mainCourses[2];
    $selectedCourse3 = $mainCourses[3];
    $selectedLCourse1 = $labCourses[1];
    $q=0;
    // condition is $c
    for($i = 1; $i < $c; $i++){
        $effi=0;
        $schedule = [
            [0,0,0,0,0,0,0,0],
            [0,0,0,0,0,0,0,0],
            [0,0,0,0,0,0,0,0],
            [0,0,0,0,0,0,0,0],
            [0,0,0,0,0,0,0,0]
        ];
        // echo ${'combo'.$i};

        $selectedSec = ${'combo'.$i}[0];
        $selectedSec1 = ${'combo'.$i}[1];
        $selectedSec2 = ${'combo'.$i}[2];
        $selectedSec3 = ${'combo'.$i}[3];

        //get time and date of seclected section of the course
        $selectedDayTime = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedCourse` WHERE Section = '$selectedSec'"),MYSQLI_ASSOC);
        $selectedLDayTime = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedLCourse` WHERE Section = '$selectedSec'"),MYSQLI_ASSOC);
        $selectedDayTime1 = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedCourse1` WHERE Section = '$selectedSec1'"),MYSQLI_ASSOC);
        $selectedDayTime2 = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedCourse2` WHERE Section = '$selectedSec2'"),MYSQLI_ASSOC);
        $selectedDayTime3 = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedCourse3` WHERE Section = '$selectedSec3'"),MYSQLI_ASSOC);
        $selectedLDayTime1 = mysqli_fetch_all(mysqli_query($db, "SELECT Day,Time FROM `$selectedLCourse1` WHERE Section = '$selectedSec3'"),MYSQLI_ASSOC);
        $effi += fillSchedule($selectedDayTime);
        $effi += fillSchedule($selectedLDayTime);
        $effi += fillSchedule($selectedDayTime1);
        $effi += fillSchedule($selectedDayTime2);
        $effi += fillSchedule($selectedDayTime3);
        $effi += fillSchedule($selectedLDayTime1);

        $conflict = 0;
        for($k = 0; $k < sizeof($schedule); $k++){
            for($j = 0; $j < sizeof($schedule[0]); $j++){
                if($schedule[$k][$j] > 1){
                    $conflict = 1;
                    break 2;
                }
            }
        }

        if($conflict == 0){
            echo '(Score: '.$effi.')  '.'<strong>'.${'combo'.$i}.'</strong>'.json_encode($schedule).'<br><br>';
        }
        
    }

    
    //echo '<br>'.'<br>'.'<br>'.'<br>'.json_encode($schedule);



?>
