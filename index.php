<?php

/*==============//
//   includes   //
//==============*/
include 'Database.php';


/*=============//
//   globals   //
//=============*/
//cached db results
$mainDishesBreakfast = array();
$mainDishesLunch = array();
$mainDishesDinner = array();
$sideDishesBreakfast = array();
$sideDishesLunch = array();
$sideDishesDinner = array();
$snacks = array();

//queries
$FOOD_TYPE_QUERY = <<<MULTILINE_STRING
SELECT
	*
FROM food
WHERE food_id IN (
	SELECT
		food_id
	FROM food_type_link
	JOIN food_type USING (food_type_id)
	WHERE food_type.name = "%s"
)

MULTILINE_STRING;

$FOOD_QUALIFIER_CLAUSE = <<<MULTILINE_STRING
AND food_id %s IN (
	SELECT
		food_id
	FROM food_qualifier_link
	JOIN food_qualifier USING (food_qualifier_id)
	WHERE food_qualifier.name = "%s"
)

MULTILINE_STRING;


/*===============//
//   utilities   //
//===============*/
function getAllFoods($db)
{
	$query = "SELECT * FROM food ORDER BY name;";
	$results = $db->runAndReturn($query);
	return $results;
}
function printResults($results)
{
	$i = 1;
	echo "<ul>";
	while($row = $results->fetchArray())
	{
		echo "<li>Row ".$i."<ul>";
		$i++;
		foreach($row as $column_name => $column_value)
		{
			echo "<li>".$column_name.": ".$column_value."</li>";
		}
		echo "</ul></li>";
	}
	echo "<ul>";
}
function getRandomBreakfastFoods($db)
{
	/* returns a list of random breakfast foods */
	
	//get globals
	global $FOOD_TYPE_QUERY;
	global $FOOD_QUALIFIER_CLAUSE;
	
	//cached query results
	global $mainDishesBreakfast;
	global $sideDishesBreakfast;
	
	//local variables
	$foods = array();
	
	//check if query results have already been cached, and run if not
	if(count($mainDishesBreakfast) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Main');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'','Breakfast');
		$mainDishesBreakfast = $db->runAndReturnArray($query);
	}
	if(count($sideDishesBreakfast) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Side');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'','Breakfast');
		$sideDishesBreakfast = $db->runAndReturnArray($query);
	}
	
	//get random main breakfast dish
	$randomIndex = array_rand($mainDishesBreakfast);
	$foods[] = $mainDishesBreakfast[$randomIndex]['name'];
	
	//get random side breakfast dish
	$randomIndex = array_rand($sideDishesBreakfast);
	$foods[] = $sideDishesBreakfast[$randomIndex]['name'];
	
	return $foods;
}
function getRandomLunchFoods($db)
{
	/* returns a list of random lunch foods */
	
	//get globals
	global $FOOD_TYPE_QUERY;
	global $FOOD_QUALIFIER_CLAUSE;
	
	//cached query results
	global $mainDishesLunch;
	global $sideDishesLunch;
	
	//local variables
	$foods = array();
	
	//check if query results have already been cached, and run if not
	if(count($mainDishesLunch) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Main');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'','Quick');
		$mainDishesLunch = $db->runAndReturnArray($query);
	}
	if(count($sideDishesLunch) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Side');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'','Quick');
		$sideDishesLunch = $db->runAndReturnArray($query);
	}
	
	//get random main lunch dish
	$randomIndex = array_rand($mainDishesLunch);
	$foods[] = $mainDishesLunch[$randomIndex]['name'];
	
	//get random side lunch dish
	$randomIndex = array_rand($sideDishesLunch);
	$foods[] = $sideDishesLunch[$randomIndex]['name'];
	
	return $foods;
}
function getRandomDinnerFoods($db)
{
	/* returns a list of random dinner foods */
	
	//get globals
	global $FOOD_TYPE_QUERY;
	global $FOOD_QUALIFIER_CLAUSE;
	
	//cached query results
	global $mainDishesDinner;
	global $sideDishesDinner;
	
	//local variables
	$foods = array();
	
	//check if query results have already been cached, and run if not
	if(count($mainDishesDinner) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Main');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'NOT','Breakfast');
		$mainDishesDinner = $db->runAndReturnArray($query);
	}
	if(count($sideDishesDinner) < 1)
	{
		$query = sprintf($FOOD_TYPE_QUERY,'Side');
		$query = $query . sprintf($FOOD_QUALIFIER_CLAUSE,'NOT','Breakfast');
		$sideDishesDinner = $db->runAndReturnArray($query);
	}
	
	//get random main dinner dish
	$randomIndex = array_rand($mainDishesDinner);
	$foods[] = $mainDishesDinner[$randomIndex]['name'];
	
	//get random 1st side dinner dish
	$randomIndex = array_rand($sideDishesDinner);
	$foods[] = $sideDishesDinner[$randomIndex]['name'];
	
	//get random 2nd side dinner dish
	$newRandomIndex = $randomIndex;
	while($newRandomIndex == $randomIndex)
	{
		$newRandomIndex = array_rand($sideDishesDinner);
	}
	$foods[] = $sideDishesDinner[$newRandomIndex]['name'];
	
	return $foods;
}
function getRandomWeeklyMenu($db)
{
	/* creates a full week of random meals */
	
	//initialize weekly menu
	$menu = array("Monday"=>array(),
	              "Tuesday"=>array(),
	              "Wednesday"=>array(),
	              "Thursday"=>array(),
	              "Friday"=>array(),
	              "Saturday"=>array(),
	              "Sunday"=>array());
	
	//loop through week and populate
	foreach($menu as $day => $meals)
	{
		//get breakfast foods
		$breakfastFoods = getRandomBreakfastFoods($db);
		$menu[$day]['Breakfast'] = $breakfastFoods;
		
		//get lunch foods
		$lunchFoods = getRandomLunchFoods($db);
		$menu[$day]['Lunch'] = $lunchFoods;
		
		//get dinner foods
		$dinnerFoods = getRandomDinnerFoods($db);
		$menu[$day]['Dinner'] = $dinnerFoods;
	}
	
	return $menu;
}
function printMenu($menu)
{
	/* outputs each day and meal of a menu */
	
	//loop through days of menu
	echo "\n"; //blank line
	foreach($menu as $day => $meals)
	{
		//label the day
		echo "<div class=\"day\">\n";
		echo "\t<label for=\"$day\">$day</label>\n";
		
		//start day
		echo "\t<div class=\"meals\" id=\"$day\">\n";
		
		//show meals
		foreach($meals as $name => $foods)
		{
			//label the meal
			echo "\t\t<label for=\"".$day."_$name\">$name</label>\n";
			
			//start meal
			echo "\t\t<div class=\"meal\" id=\"".$day."_$name\">\n\t\t\t<ul>\n";
			
			//show foods
			foreach($foods as $food)
			{
				echo "\t\t\t\t<li>$food</li>\n";
			}
			
			//end meal
			echo "\t\t\t</ul>\n\t\t</div>\n";
		}
		
		//end day
		echo "\t</div>\n";
		echo "</div>\n";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Matteorr Random Menu Planner</title>
		<meta content="width=device-width, initial-scale=0.6666, maximum-scale=1.0, minimum-scale=0.6666" name="viewport">
		<link rel="stylesheet" type="text/css" href="menu.css">
	</head>
	<body>
		<div id="content_container">
			<div id="page_heading_container">
				<h1>Matteorr Random Menu Planner</h1>
			</div>
			<div id="page_content">
				<div class="article">
					<?php
						//open database
						$db = new Database();
						$success = $db->open();
						if(!$success)
						{
							echo "ERROR: Failed to connect to database";
						}
						else
						{
							//get random menu
							$menu = getRandomWeeklyMenu($db);
							
							//output results
							printMenu($menu);
							
							//close db connection
							$db->close();
						}
					?>
				</div>
			</div>
			<div id="page_footer_container">
				<div id="page_footer">
					<div>Site © Scopegrope.com</div>
				</div>
			</div>
		</div>
	</body>
</html>

