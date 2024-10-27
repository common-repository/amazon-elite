<?php
require_once ('Amazon_Elite_API_Functions.php');

$searchDataContent = "";

$delimiter = ",";

$DBWD_search_data_filename = "searchData.dat";

$selectCountry = "";
$selectRootCategory = "";
$categoryDrillDown = "";
$categoryDrillDownCount = "";
$searchFor = "";
$selectItemCount = "";
$browse_node_id = "";
$categoryID = "";
$pluginURL = "";    
$categoryListArray = array();
$currentTimeStamp = gmdate('Y-m-d\TH:i:s\Z');
$accessKeyID = "";
$secretKey = "";
$associateTag = "";

$DBWD_last_CSV_filename='Amazon_Elite_' . $searchFor . '_' . gmdate('Y-m-d\_H:i:s') . '.csv';
$DBWD_resultFile = "searchResult.csv";
$DBWD_resultFileDown = "searchResultDownload.csv";

$passCategory="Books";

$searchBNID=$browse_node_id;

function readSearchDataFromFile()
	{
	global $searchDataContent,$DBWD_search_data_filename,$accessKeyID,$secretKey,$associateTag;
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
	for ($a=0; $a<($categoryDrillDownCount-1); $a++)
		{
		$categoryListArray[$a] = $fileDataWorkArray[$a+$preInfoOffset];
		}

	}

	function getProductInfo($passCategory,$searchFor,$searchBNID,$DBWD_last_CSV_filename)
		{
		global $searchDataContent,$DBWD_search_data_filename,$accessKeyID,$secretKey,$associateTag,$pluginURL;
		global $selectCountry,$selectRootCategory,$categoryDrillDown,$categoryDrillDownCount,$currentTimeStamp;
		global $selectItemCount,$browse_node_id,$categoryID,$pluginURL,$categoryListArray,$DBWD_resultFile,$DBWD_resultFileDown,$delimiter;

		$loopDBWDlimit=$selectItemCount/10;
		$ItemLoopCount=0;
		$SalesRankFailCount=0;
		$MerchantFailCount=0;

		$ASIN_Out="";
		$ItemTitle="";
		$ItemPrice="";
		$SalesRankOut="";
		$MerchantOut="";

		if ($searchFor == "") $searchFor="All";

		$searchWords = $searchFor;
		$searchWords=str_replace(" ",",",$searchWords);

		$searchCategory=$passCategory;
		$search_index = "Books";
		
		$response_group = "OfferFull,ItemAttributes,SalesRank";

		$DBWD_last_CSV_filename_out = $DBWD_resultFile;
		$DBWD_last_CSV_filename_download_out = $DBWD_resultFileDown;

		$item_page = $loopDBWDlimit;

		$handle = fopen($DBWD_last_CSV_filename_out, 'w');
		$handleCSV = fopen($DBWD_last_CSV_filename_download_out, 'w');

		for ($loopDBWD=1; $loopDBWD<=$loopDBWDlimit; $loopDBWD++)
			{
			$itemResponse = item_search($searchWords, $search_index, $browse_node_id, $loopDBWD, $accessKeyID, $secretKey, $associateTag, $response_group, $currentTimeStamp, $selectCountry);

			$returnCount = count($itemResponse);

			if (!$returnCount) { fclose($handleCSV); fclose($handle); return; }

			if (isset($itemResponse['ItemSearchResponse']['Items']['Item'])) 
				{
				foreach($itemResponse['ItemSearchResponse']['Items']['Item'] as $item_key => $item)
					{
					if (is_string($item_key)) { fclose($handleCSV); fclose($handle); return; }

					if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['SalesRank']))
						{ $SalesRankOut="{ NA }"; $SalesRankFailCount++; }
					else
						{
						$SalesRankOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['SalesRank'];
						$SalesRankOut=str_replace(","," ",$SalesRankOut);
						}
         	
					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['TotalOffers']))
						{
						if ($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['TotalOffers'] == 1)
							{
							if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['Merchant']['Name']))
								{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
							else
								{
								$MerchantOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['Merchant']['Name'];
								$MerchantOut=str_replace(","," ",$MerchantOut);
								}
							}
						else
							{
							if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['0']['Merchant']['Name']))
								{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
							else
								{
								$MerchantOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['0']['Merchant']['Name'];
								$MerchantOut=str_replace(","," ",$MerchantOut);
								}
							}
						}
					else
						{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
         	
					$ASIN_Out=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ASIN'];
					$ASIN_Out=str_replace(","," ",$ASIN_Out);
         	
					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['Title'],$itemResponse))
						{
						$ItemTitle=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['Title'];
						$ItemTitle=str_replace(","," ",$ItemTitle);
						}

					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['SalePrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['SalePrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
 					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Transaction']['TransactionItems']['TransactionItem']['TotalPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
 					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['EligibilityRequirements']['EligibilityRequirement']['CurrencyAmount'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestCollectiblePrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestUsedPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					else 
						{
						$ItemPrice=" { NA }";
						}
         	
					$ItemLinkPage=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['DetailPageURL'];
         	
					if ($ItemPrice!="Too low to display")
						{
						$ItemPrice=substr($ItemPrice, 1, strlen($ItemPrice)-1);
						$ItemPriceOut=str_replace(" ","",$ItemPrice);
						}

        			if ($ItemPriceOut=="{NA}") { $ItemPriceOut="{ NA }"; }

					$ItemLoopCount++;

					# Output result file
					         	
					fwrite($handleCSV, $ItemLoopCount . $delimiter);
					fwrite($handleCSV, $ItemTitle . $delimiter);
					fwrite($handleCSV, $ASIN_Out . $delimiter);
					fwrite($handleCSV, $MerchantOut . $delimiter);
					fwrite($handleCSV, $ItemPriceOut . $delimiter);
					fwrite($handleCSV, $SalesRankOut . "\n");
         	
					fwrite($handle, $ItemLoopCount . $delimiter);
					fwrite($handle, $ItemTitle . $delimiter);
					fwrite($handle, $ASIN_Out . $delimiter);
					fwrite($handle, $MerchantOut . $delimiter);
					fwrite($handle, $ItemPriceOut . $delimiter);
					fwrite($handle, $SalesRankOut . $delimiter);
					fwrite($handle, "View^" . $ItemLinkPage . "^_blank\n");
         	
					}
				}
			}

		fclose($handleCSV);
		fclose($handle);
		}
				
?>

<html>
	<head>
		<script type="text/javascript">
			window.onload=function()
				{
				var ele = document.getElementById("update");
				ele.style.display = "none";
				}
		</script>
	</head>

	<body style="margin:0;padding:0" bgcolor=#ffffff>
		
		<?php 
		 	readSearchDataFromFile();
			if ($categoryDrillDown == "")
				{
				echo("<center><br><font size=2 color=navy>Select a Category (and any desired sub categories) and Search to Continue</font>");
				echo("</center></body></html>");
				exit;
				}
		?>

		<div id="gridText1" style="position:relative; width:860px; height:60px; top:0px; border: 1px solid #a4bed4; background-color: #e3efff; -moz-border-radius: 5px 5px 0px 0px;; border-radius: 5px 5px 0px 0px;" align=left>
			<div id="searchResultCount0" name="searchResultCount0" style="position:absolute; top:4px; left:8px;">
				<font size=2 color="#000000"><b>Result Count:</b></font>
			</div>

			<div id="update" name="update" style="position:absolute; top:4px; left:380px;">
				<img src="<?php print $pluginURL ?>gifs/loading_bar.gif" height=24>
			</div>

			<div id="searchResultCount1" name="searchResultCount1" style="position:absolute; top:34px; left:53px;">
					<font size=1 color="#000080" style="vertical-align:text-bottom;">Clicking the 'Column Headers' will Sort - change most 'Column Widths' by moving Column Dividers - { NA } is Data 'Not Available' from Amazon - Click 'View' for Item Detail Page</font>
			</div>

			<?php
				getProductInfo($passCategory,$searchFor,$searchBNID,$DBWD_last_CSV_filename);
				$linecount = count(file($DBWD_resultFile));
				$gridheight=($linecount*20)+25; 
		 	?>

			<div id="searchResultCount2" name="searchResultCount2" style="position:absolute; top:4px; left:87px;">
				<font size=2 color="#000000"><?php print $linecount ?></font>
			</div>

		</div>
	
		<div id="mygrid_container" style="position:relative; width:860px; height:<?php echo $gridheight ?>px; top:-1px; border: 1px solid #a4bed4;"></div>
		
    	<link rel="stylesheet" type="text/css" href="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/dhtmlxgrid.css">
		<link rel="stylesheet" type="text/css" href="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>
    	<script type="text/javascript" src="<?php print $pluginURL ?>js/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
 
		<script type="text/javascript">
			var mygrid;
			function doInitGrid()
				{
				mygrid = new dhtmlXGridObject('mygrid_container');
				mygrid.setImagePath("<?php print $pluginURL ?>js/dhtmlxGrid/codebase/imgs/");		//path to images required by grid
				mygrid.setHeader("#, Item Title, ASIN, Sold By, Price, SalesRank, View");			//set column names
				mygrid.setInitWidths("40,*,100,120,60,80,40");												//set column width in px
				mygrid.setColAlign("left,left,left,left,left,left,center");								//set column values align
				mygrid.enableStableSorting(true)
				mygrid.setColSorting("int,str,str,str,int,int,na");
				mygrid.sortRows(1, "int", "asc");
				mygrid.setColTypes("ro,ro,ro,ro,ro,ro,link");
				mygrid.init();																							//initialize grid
				mygrid.setSkin("dhx_skyblue");																	//set grid skin
				mygrid.load("<?php print $pluginURL . $DBWD_resultFile ?>","csv");
				mygrid.setSortImgState(true,0);
				}
			doInitGrid();
		</script>


	</body>
</html>

<?php ?>