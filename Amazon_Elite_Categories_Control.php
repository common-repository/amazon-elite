<?php

require_once ('Amazon_Elite_API_Functions.php');

$pass = $_GET['pass'];
$selectCountry = $_GET['selectCountry'];
$selectRootCategory = $_GET['selectRootCategory'];
$accessKeyID = $_GET['accessKeyID'];
$secretKey = $_GET['secretKey'];
$associateTag = $_GET['associateTag'];
$categoryDrillDown = $_GET['categoryDrillDown'];
$categoryDrillDownCount = $_GET['categoryDrillDownCount'];
$categoryListArray = (array)$_GET['categoryListArray'];
$searchFor = $_GET['searchFor'];
$selectItemCount = (((600-100)/500)*5)*2;

$pluginURL = $_GET['pluginURL'];

$currentTimeStamp = gmdate('Y-m-d\TH:i:s\Z');

$DBWD_search_data_filename = "searchData.dat";
$DBWD_search_data_save_filename = "./saved-searches/";

$browse_node_id = $selectRootCategory;
$browse_node_id_hold=0;
$response_group = "BrowseNodeInfo";
$nodeURLresponse = "";

$locale = $selectCountry;

$noSave=0;

$nodeListArray = array();

processCategoryPass($pass,$locale);

function processCategoryPass($pass,$locale)
	{
	global $selectRootCategory,$categoryDrillDownCount,$categoryDrillDown,$nodeURLresponse,$categoryListArray;
	global $browse_node_id, $response_group, $accessKeyID, $secretKey, $associateTag, $currentTimeStamp,$locale,$pass;
	global $selectItemCount,$searchFor,$DBWD_search_data_filename,$DBWD_search_data_save_filename,$browse_node_id_hold;
	
	if($pass == '0')
		{
		if ($categoryDrillDownCount == '0') { $searchFor="All"; }
		$categoryDrillDown = "";
		$categoryListArray=array();
		readSearchDataFromFile();
		}

	if($pass == 'saveSearchCopy')
		{
		saveSearchDataToFile();
			
		$browse_node_id = $categoryListArray[$categoryDrillDownCount-1];

		$temporary = array();

		for($a=0; $a<count($categoryListArray)-1; $a++)
			{
			$temporary[$a] = $categoryListArray[$a];
			}

		$categoryListArray = array();
		$categoryListArray = $temporary;
		
		$categoryListWorkArray = array();
		$categoryListWorkArray = explode(' - ', $categoryDrillDown);
		$categoryListWorkArrayHold = $categoryListWorkArray;

		$temporary = array_pop($categoryListWorkArray);

		$categoryDrillDown = "";
		$categoryDrillDown = implode(' - ', $categoryListWorkArray);
		
		$currentTimeStampWork = str_replace("T","_",$currentTimeStamp);
		$currentTimeStampWork = str_replace("Z","",$currentTimeStampWork);

		$DBWD_search_data_save_filename .= strtoupper($locale) . "_" . implode('_', $categoryListWorkArrayHold) .  "_" . $searchFor . "_" . $currentTimeStampWork . ".dat";
		
		copy($DBWD_search_data_filename, $DBWD_search_data_save_filename);
		?>
		
		<script type="text/javascript">
			parent.frames['displaySavedSearches'].location.href=parent.frames['displaySavedSearches'].location.href = 'Amazon_Elite_Saved_Searches.php?pass=1';
		</script>
		<?php
		
		}

	if($pass == 'submitted')
		{
		$browse_node_id = $categoryListArray[$categoryDrillDownCount-1];
		$temporary = array();

		for($a=0; $a<count($categoryListArray)-1; $a++)
			{
			$temporary[$a] = $categoryListArray[$a];
			}

		$categoryListArray = array();
		$categoryListArray = $temporary;
		
		$categoryListWorkArray = array();
		$categoryListWorkArray = explode(' - ', $categoryDrillDown);

		$temporary = array_pop($categoryListWorkArray);

		$categoryDrillDown = "";
		$categoryDrillDown = implode(' - ', $categoryListWorkArray);
		}

	if($pass == 'selectCategory')
		{
		$categoryDrillDownCount++;
		}

	if($pass == 'previousCategory')
		{
		if ($categoryDrillDownCount != '0') { $categoryDrillDownCount--; };
		if ($categoryDrillDownCount == '0') { $searchFor="All"; }
		
		$browse_node_id = $categoryListArray[$categoryDrillDownCount-1];
		$selectRootCategory = $categoryListArray[$categoryDrillDownCount];

		$temporary = array();

		for($a=0; $a<count($categoryListArray)-2; $a++)
			{
			$temporary[$a] = $categoryListArray[$a];
			}

		$categoryListArray = array();
		$categoryListArray = $temporary;
		
		$categoryListWorkArray = array();
		$categoryListWorkArray = explode(' - ', $categoryDrillDown);

		$temporary = array_pop($categoryListWorkArray);

		$categoryDrillDown = "";
		$categoryDrillDown = implode(' - ', $categoryListWorkArray);
		}

	if($pass == 'resetCategory')
		{
		$categoryDrillDown = "";
		$categoryDrillDownCount = 0;
		$selectRootCategory = "";
		$browse_node_id = "";
		$searchFor="All";
		}

	if ($pass == 'selectCountryPass')
		{
		$categoryDrillDown = "";
		$categoryDrillDownCount = 0;
		$selectRootCategory = "";
		$browse_node_id = "";
		$searchFor="All";
		}

	if ($categoryDrillDownCount == '0')
		{
		$categoryDrillDown = "";
		$categoryListArray=array();
		}
	}	

function readSearchDataFromFile()
	{
	global $searchDataContent,$DBWD_search_data_filename,$accessKeyID,$secretKey,$associateTag,$locale;
	global $selectCountry,$selectRootCategory,$categoryDrillDown,$categoryDrillDownCount,$currentTimeStamp;
	global $searchFor,$selectItemCount,$browse_node_id,$categoryID,$pluginURL,$categoryListArray;
	
	$DBWD_search_data_filename_out = $DBWD_search_data_filename;

	$handle = @fopen($DBWD_search_data_filename_out, 'r');
	if (!$handle) return;

	$searchDataContent = fread($handle, filesize($DBWD_search_data_filename_out));

	fclose($handle);

	$fileDataWorkArray = array();
	$fileDataWorkArray = explode('|', $searchDataContent);

	$selectCountry = $fileDataWorkArray[0];
	$selectRootCategory = $fileDataWorkArray[1];
	$categoryDrillDown = $fileDataWorkArray[2];
	$categoryDrillDownCount = $fileDataWorkArray[3];
	$searchFor = $fileDataWorkArray[4];
	$selectItemCount = $fileDataWorkArray[5];
	$categoryID = $fileDataWorkArray[6];
	$browse_node_id = $fileDataWorkArray[7];
	$pluginURL = $fileDataWorkArray[8];
#	$currentTimeStamp = $fileDataWorkArray[9];
	$accessKeyID = $fileDataWorkArray[10];
	$secretKey = $fileDataWorkArray[11];
	$associateTag = $fileDataWorkArray[12];
	
	$preInfoOffset = 13;
	$categoryListArray = array();
	for ($a=0; $a<($categoryDrillDownCount-1); $a++)
		{
		$categoryListArray[$a] = $fileDataWorkArray[$a+$preInfoOffset];
		}

	#$browse_node_id = $selectRootCategory;
	
	$locale = $selectCountry;
	}

function browseNodeAcquire($browse_node_id, $response_group, $accessKeyID, $secretKey, $associateTag, $currentTimeStamp, $locale)
	{
	global $pass,$locale_endpoints,$categoryDrillDown,$categoryDrillDownCount,$nodeListArray,$categoryListArray,$browse_node_id_hold;
	global $noSave;
	
	if ($browse_node_id == "") { $nodeURLresponse = ""; return $nodeURLresponse; }
	
	array_push($categoryListArray,$browse_node_id);	
	
	$urlLocale = (array)$locale_endpoints[$locale];

	$nodeURLwork = $urlLocale[0] . "?Service=AWSECommerceService&AWSAccessKeyId=" . $accessKeyID . "&AssociateTag=" . $associateTag . "&Operation=BrowseNodeLookup&BrowseNodeId=" . $browse_node_id . "&ResponseGroup=" . $response_group . "&Timestamp=" . $currentTimeStamp;
	
	$nodeURL = signAmazonUrl($nodeURLwork, $secretKey);

	if (!$nodeURLresponse = @file_get_contents($nodeURL))
		{
		$noSave=1;
		return "";
		}

	$nodeURLresponse = xml2array($nodeURLresponse);
	
	$loopCount=0;
	while ($loopCount<30)
		{
		if ($nodeURLresponse[BrowseNodeLookupResponse][BrowseNodes][BrowseNode][Children][BrowseNode][$loopCount] == "") break;
	
		$workTemp1 = $nodeURLresponse[BrowseNodeLookupResponse][BrowseNodes][BrowseNode][Children][BrowseNode][$loopCount][Name];
		
		$nodeListArray[$nodeURLresponse[BrowseNodeLookupResponse][BrowseNodes][BrowseNode][Children][BrowseNode][$loopCount][BrowseNodeId]] = $workTemp1;

		$loopCount++;
		}

	if(($pass != 'previousCategory')&&($pass != '0'))
		{
		if ($categoryDrillDownCount > 1) $categoryDrillDown .= " - "; 
		
		$workTemp = "";
		$workTemp = $nodeURLresponse[BrowseNodeLookupResponse][BrowseNodes][BrowseNode][Name];

		$categoryDrillDown .= $workTemp;
		}

	return $nodeURLresponse;
	}

function displayCountriesListBox($selectCountry)
	{
	$country_LB_Out = "Select Country: 
		<select style='width:124px; z-index:100;' name='selectCountry' onchange='passVariableSubmit(\"selectCountryPass\")'>
			<option value='us'";
			if ($selectCountry == 'us') $country_LB_Out .= ' selected'; $country_LB_Out .= ">United States</option>
			<option value='ca'";
			if ($selectCountry == 'ca') $country_LB_Out .= ' selected'; $country_LB_Out .= ">Canada</option>
			<option value='fr'";
			if ($selectCountry == 'fr') $country_LB_Out .= ' selected'; $country_LB_Out .= ">France</option>
			<option value='de'";
			if ($selectCountry == 'de') $country_LB_Out .= ' selected'; $country_LB_Out .= ">Germany</option>";
			
#			$country_LB_Out .= "<option value='es'";
#			if ($selectCountry == 'es') $country_LB_Out .= ' selected'; $country_LB_Out .= ">Spain</option>";
#			<option value='it'";
#			if ($selectCountry == 'it') $country_LB_Out .= ' selected'; $country_LB_Out .= ">Italy</option>
#			<option value='cn'";
#			if ($selectCountry == 'cn') $country_LB_Out .= ' selected'; $country_LB_Out .= ">China</option>


			$country_LB_Out .= "<option value='jp'";
			if ($selectCountry == 'jp') $country_LB_Out .= ' selected'; $country_LB_Out .= ">Japan</option>
			<option value='uk'";
			if ($selectCountry == 'uk') $country_LB_Out .= ' selected'; $country_LB_Out .= ">United Kingdom</option>
		</select>";	

	return $country_LB_Out;
	}

function displayCategoryListBox($locale)
	{
	global $selectRootCategory, $categoryDrillDownCount, $browse_node_id, $nodeListArray;

	if ($categoryDrillDownCount == 0) $indexes = (array)get_locale_browse_node_indexes($locale);
	else $indexes = (array)$nodeListArray;
	
	if ((count($indexes)) != 0)
		{
		$categoryRoot_LB_Out = "<select style='width:190px; z-index:100;' name='selectRootCategory'>";
		
		foreach ($indexes as $key => $value) 
			{
			if ($selectRootCategory == "") { $browse_node_id = $key; $selectRootCategory = $key; }
			$categoryRoot_LB_Out .= "<option value='" . $key . "'";
			if ($selectRootCategory == $key) { $categoryRoot_LB_Out .= " selected"; $browse_node_id = $key; $selectRootCategory = $key; }
			$categoryRoot_LB_Out .= ">" . $value . "</option>";
			}
		
		$categoryRoot_LB_Out .= "</select>";	
		}
	else
		{
		$categoryRoot_LB_Out = 
			"<div style='position:absolute; background-color:#e3efff; top:0px; left:104px; width:190px; text-align:center; border: 0px solid #a4bed4; height=14px;'>
			<font size=3 color='#000000'>( End of Category List )&nbsp;</font></div>";
		}

	return $categoryRoot_LB_Out;
	}

function displaySaveSearchButton()
	{
	global $categoryDrillDown,$nodeListArray;
	
	if($categoryDrillDown != "")
		{
		echo '<button name="saveSearchCopy" style="width:140px; height:23px;" onclick="passVariableSubmit(\'saveSearchCopy\')">Save Search</button>';
		}
	else
		{
		echo '<button name="saveSearchCopy" style="width:140px; height:23px;" disabled>Save Search</button>';
		}
	}

function displayCategoryListButtons()
	{
	global $categoryDrillDown,$nodeListArray,$pass;
	
	echo '<div style="position:absolute; top:-1px; left: 300px; border:0px solid #a4bed4; ">';
	
	if((count($nodeListArray) > 0)||(($categoryDrillDown == "")))
		{
		echo '<button name="selectCategory" style="width:80px; height:23px;" onclick="passVariableSubmit(\'selectCategory\')">Select</button>';
		}
	else
		{
		echo '<button name="selectCategory" style="width:80px; height:23px;" disabled>Select</button>';
		}

	if ($categoryDrillDown != "")
		{
		echo '<button name="previousCategory" style="width:80px; height:23px;" onclick="passVariableSubmit(\'previousCategory\')">Previous</button>';
		echo '<button name="resetCategory" style="width:80px; height:23px;" onclick="passVariableSubmit(\'resetCategory\')">Reset</button>';
		}
	else
		{
		echo '<button name="previousCategory" style="width:80px; height:23px;" disabled>Previous</button>';
		echo '<button name="resetCategory" style="width:80px; height:23px;" disabled>Reset</button>';
		}
	echo '</div>';
	}

function displayFullSearchDetails()
	{
	global $categoryDrillDown;
	$getLength = 130;
	
	echo '<font size=2 color=black>';
	if ($categoryDrillDown == "")
		echo 'No Category Selected';
	else
		{ 
		$strOutput = $categoryDrillDown;
		
		$strWork = strlen($categoryDrillDown);
		
		if ($strWork > $getLength)
			{
			$strOutput = '... ';
			$strOutput .= substr($categoryDrillDown,($strWork-$getLength));
			}
		
		echo $strOutput;
		}
	echo '</font>';
	}


function saveSearchDataToFile()
	{
	global $pass,$selectCountry,$selectRootCategory,$accessKeyID,$secretKey,$associateTag,$categoryDrillDown,$pluginURL;
	global $DBWD_search_data_filename,$categoryDrillDownCount,$categoryListArray,$browse_node_id,$searchFor,$selectItemCount;
	global $currentTimeStamp;
	
	$DBWD_search_data_filename_out = $DBWD_search_data_filename;

	$handle = fopen($DBWD_search_data_filename_out, 'w');

	fwrite($handle, $selectCountry . '|');
	fwrite($handle, $selectRootCategory . '|');
	fwrite($handle, $categoryDrillDown . '|');
	fwrite($handle, $categoryDrillDownCount . '|');
	fwrite($handle, $searchFor . '|');
	fwrite($handle, $selectItemCount . '|');
	fwrite($handle, $browse_node_id . '|');
	fwrite($handle, $categoryListArray[$categoryDrillDownCount-1] . '|');
	fwrite($handle, $pluginURL . '|');
	fwrite($handle, $currentTimeStamp . '|');
	fwrite($handle, $accessKeyID . '|');
	fwrite($handle, $secretKey . '|');
	fwrite($handle, $associateTag . '|');

	for ($a=0; $a<$categoryDrillDownCount; $a++)
		{
		fwrite($handle, $categoryListArray[$a] . '|');
		}

	fclose($handle);
	}

function diagDisplay()
	{
	global $selectRootCategory,$categoryDrillDownCount,$categoryDrillDown,$nodeURLresponse,$categoryListArray;
	global $browse_node_id, $response_group, $accessKeyID, $secretKey, $associateTag, $currentTimeStamp, $locale,$pass;
	global $selectCountry;
		
	echo '<div style="position:relative; top:70px; left:435px; width:410px; border:0px solid #a4bed4;">
		[pass: ' . $pass . ']<br>' . '
		[selectCountry: ' . $selectCountry . ']<br>' . '
		[selectRootCategory: ' . $selectRootCategory . ']<br>' . '
		[categoryDrillDown: ' . $categoryDrillDown . ']<br>' . '
		[categoryDrillDownCount: ' . $categoryDrillDownCount . ']';
	
	echo '<br><br>';
	
	echo '[categoryListArray Count: ' . count($categoryListArray) . ']<br>';
	if (count($categoryListArray)>0)
		{
		for ($a=0; $a<sizeof($categoryListArray); $a++)
			{
			echo '<br>Value: ' . $categoryListArray[$a];
		
			}
		}
	
	echo '<br><br>';
	
	var_dump($categoryListArray);
	
	echo '</div>';		
	}
?>

<html>
	<head>
		<script type="text/javascript">
			function passVariableSubmit(passVariable)
				{
				document.DBWDcategory_form.pass.value = passVariable;
				document.forms["DBWDcategory_form"].submit();	
	
				var ele = document.getElementById("update");
				ele.style.display = "block";
				}
				
			window.onload=function()
				{
				var ele = document.getElementById("update");
				ele.style.display = "none";
				}
			
			function resetSearchInput() 
				{
    			document.getElementById("searchFor").value = "All";
				}
				
			function stopRKey(evt) 
				{
  				var evt = (evt) ? evt : ((event) ? event : null);
  				var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
  				if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
				}

			document.onkeypress = stopRKey; 
			
			<?php if ($pass == 'submitted')
				{ ?>
				parent.frames['displayGraphFrame'].location.reload();
			<?php } ?>
			
		</script>
	</head>

	<body style="margin:0;padding:0" bgcolor=#e3efff>

		<?php
			$nodeURLresponse=browseNodeAcquire($browse_node_id, $response_group, $accessKeyID, $secretKey, $associateTag, $currentTimeStamp, $locale);
		?>

		<form method="get" id="DBWDcategory_form" name="DBWDcategory_form">
			<input type="hidden" name="pass" value="submitted">
			<input type="hidden" name="accessKeyID" value=<?php echo $accessKeyID; ?>>
			<input type="hidden" name="secretKey" value=<?php echo $secretKey; ?>>
			<input type="hidden" name="associateTag" value=<?php echo $associateTag; ?>>
			<input type="hidden" name="categoryDrillDown" value="<?php echo $categoryDrillDown; ?>">
			<input type="hidden" name="categoryDrillDownCount" value=<?php echo $categoryDrillDownCount; ?>>
			<input type="hidden" name="selectRootCategory" value=<?php echo $selectRootCategory; ?>>
			<input type="hidden" name="pluginURL" value="<?php echo $pluginURL; ?>">

			<?php 
			foreach($categoryListArray as $value)
				{
  				echo '<input type="hidden" name="categoryListArray[]" value="'. $value. '">';
				} 
			?>

			<div id="update" name="update" style="position:absolute; top:4px; right:10px;">
				<font size=1 color="#8080a0">Updating</font>
			</div>

			<div id="searchCountry" style="position:absolute; border: 0px solid #a4bed4; width:224px; height:20px; top:0px; left:0px; text-align:left;">
				<?php echo displayCountriesListBox($selectCountry); ?>
			</div>

			<div id="searchCategories" style="position:absolute; border: 0px solid #a4bed4; width:600px; height:22px; top:0px; right:0px; text-align:left;">
				Select Category: <?php echo displayCategoryListBox($locale); echo displayCategoryListButtons(); ?>
			</div>

			<div id="fullSearchDetails" style="position:relative;  z-index:10; border: 1px solid #a4bed4; background-color:#ffffff; width:848px; height:20px; top:28px; left:0px; text-align:left; -moz-border-radius: 5px; border-radius: 5px;">
				<div style="margin: 2px; text-align:left; z-index:10;">
					<font size=2 color=black>&nbsp;<b>Category Tree:</b> </font><?php echo displayFullSearchDetails(); ?>
				</div>
			</div>

			<div id="searchText" style="position:absolute; border: 0px solid #a4bed4; width:293px; height:22px; top:58px; left:170px; text-align:right;">
				Search For: <input id="searchFor" name="searchFor" size=20 maxlength=128 style="height:21px;" onclick="this.value='';" onfocus="this.select()" onblur="this.value=!this.value?'<?php print $searchFor; ?>':this.value;" value="<?php print $searchFor; ?>" >
			</div>

			<div id "resetSearchBoxDiv" style="position:absolute; top:57px; left: 464px; border:0px solid #a4bed4; ">
				<button type="button" name="resetSearchBox" style="width:40px; height:23px;" onclick="resetSearchInput();">All&nbsp;</button>
			</div>			


			<?php
			if ($categoryDrillDown != "")
				{ ?>
				<input type="submit" name="searchSubmit" style="position:absolute; width:220px; height:24px; top:56px; left:-1px;" value="Search ">
			<?php	}
			else
				{ ?>
				<input type="submit" name="searchSubmit" style="position:absolute; width:220px; height:24px; top:56px; left:-1px;" value="No Category Selected" disabled>
			<?php	} ?>

			
			
<?php
#diagDisplay();

if ($pass=="0")
	{ ?>
	<script type="text/javascript">
		passVariableSubmit(\"submitted\");
	</script>
	<?php 
	}

 ?>
		</form>
		
	</body>
</html>

<?php
	if ($noSave==0) saveSearchDataToFile();
 ?>