<?php
require_once 'FMDataAPI.php';
require_once 'functions.php';
$settings = require 'settings.php';



$layout = 'php';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit + 1;

$searchableFields = ['författare', 'titel', 'serie', 'Årtal', 'Utgivningsort', 'Sidor', 'ISBN', 'förlag', 'Placering'];
$queries = [];
$isFiltered = false;

foreach ($searchableFields as $field) {
    $value = $_GET[$field] ?? '';
    $op = $_GET["op_$field"] ?? 'contains';
    if ($value !== '') {
        $isFiltered = true;
        switch ($op) {
            case 'equals': $queries[] = [$field => "=={$value}"]; break;
            case 'starts': $queries[] = [$field => "{$value}*"]; break;
            case 'ends':   $queries[] = [$field => "*{$value}"]; break;
            case 'not':    $queries[] = [$field => "!{$value}"]; break;
            default:       $queries[] = [$field => $value];
        }
    }
}

$link = $_GET['-link'] ?? '';
$records = [];
$error = '';
$foundCount = 0;
// Bestäm om vi ska visa "Tillbaka till sökning"-knappen
$showBackLink = !empty($queries) && ($_GET['origin'] ?? '') !== 'recordlist';

// Standardinställning för sortering
$defaultSortField = 'författare';
$defaultSortOrder = 'ascend';
$sortField = $_GET['sortfield'] ?? $defaultSortField;
$sortOrder = $_GET['sortorder'] ?? $defaultSortOrder;

// Bygg sorteringsparametrar till FileMaker API
$sort = buildSortParams($sortField, $sortOrder);


try {
    $fm = new FMDataAPI($settings['host'], $settings['database'], $settings['username'], $settings['password']);

    if ($isFiltered) {
        $matchType = $_GET['match'] ?? 'all';
        $querySet = $matchType === 'any' ? $queries : [array_merge(...$queries)];
        $response = $fm->find($layout, $querySet, [
    		'limit' => $limit,
    		'offset' => $offset,
    		'sort' => $sort
		]);
    } else {
        $response = $fm->findAll($layout, [
    		'limit' => $limit,
    		'offset' => $offset,
    		'sort' => $sort
		]);
    }

    $records = $response['data'] ?? [];
    $fields = array_keys($records[0]['fieldData']);
    $foundCount = $response['dataInfo']['foundCount'] ?? 0;


} catch (Exception $e) {
    if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), '404')) {
        // Hantera 401 (ingen match) eller 404 (ogiltig sortering) som tom sökning
        $records = [];
        $foundCount = 0;
    } else {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Lista över poster</title>
    <link rel="stylesheet" href="css/aurum.css">
    <style>
        .nav-button {
            background-color: #c89c4f;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .nav-button:hover {
            background-color: #a77d3f;
        }
    </style>
</head>
<body>
<a href="#content" class="skip-link">Hoppa till innehåll</a>
<?php include 'header.php'; ?>
<div id="wrapper">
    
    <div id="navigation">
        <?php $_GET['-link'] = 'Lista över poster'; include 'navigation.php'; ?>
    </div>
    
	<?php if (!empty($records)): ?>
    	<?php $rangeStart = $offset; $rangeEnd = $offset + count($records) - 1; ?>
	<?php endif; ?>
    
 	<div id="content">
    	<?php if (!empty($error)): ?>
        	<p class="error"><?= htmlspecialchars($error) ?></p>
    	<?php elseif (empty($records)): ?>
        	<p>Inga poster hittades.</p>
    	<?php else: ?>
        	<table class="browse_records">        
			<tr class="table_row">

			<?php foreach ($fields as $field): ?>
    			<?php
    			switch ($field) {
        			case 'ID': $label = ''; break;
        			case 'Förlag': $label = 'Ort / Förlag'; break;
        			case 'Placering': $label = 'Hyllsignum'; break;
        			case 'URL': $label = 'Librislänk'; break;
        			case 'ExtraInfo': $label = 'Anm.'; break;
        			default: $label = ucfirst($field);
    			}	
    			if ($label === '') continue;

				// Sorteringslogik
   				$currentSort = $_GET['sortfield'] ?? '';
        		$currentOrder = $_GET['sortorder'] ?? 'ascend';
        		// Om detta fält redan är aktivt sortfält → växla riktning
        		$newOrder = (strcasecmp($currentSort, $field) === 0 && $currentOrder === 'ascend')
            		? 'descend'
            		: 'ascend';
        		// Bygg ny querystring för sortering
        		$query = array_merge($_GET, ['sortfield' => $field, 'sortorder' => $newOrder]);

         		// Tillgänglighetsbeskrivning
        		$ariaLabel = "Sortera " . ($newOrder === 'ascend' ? 'stigande' : 'fallande') . " efter $label";
        		?>
        		<th class="browse_header" style="color: black;">
            		<a href="?<?= http_build_query($query) ?>"
               		aria-label="<?= $ariaLabel ?>"
               		title="<?= $ariaLabel ?>">
               		 <?= htmlspecialchars($label) ?>
            		<?= (strcasecmp($currentSort, $field) === 0)
                    	? ($currentOrder === 'ascend' ? ' ↑' : ' ↓')
                    	: '' ?>
            		</a>
        		</th>

		<?php endforeach; ?>

		<?php if (!empty($records)): ?>
    		<?php include 'navline.php'; ?>
		<?php endif; ?>
		<?php $row=0; ?>
	</tr>

    <?php foreach ($records as $record): ?>
    <?php // Bestäm klass för varannan rad (zebra)
	$rowClass = ($row % 2 == 0) ? 'browse_cell even' : 'browse_cell odd'; ?>
	<tr class="table_row">
        <?php foreach ($record['fieldData'] as $field => $val): ?>
    		<?php if ($field === 'URL' && filter_var($val, FILTER_VALIDATE_URL)): ?>
        		<td class="<?= $rowClass ?>"><a href="<?= htmlspecialchars($val) ?>" target="_blank">LIBRIS</a></td>
    		<?php else: ?>
        		<td class="<?= $rowClass ?>"><?= htmlspecialchars($val) ?></td>
    		<?php endif; ?>
		<?php endforeach; ?>			   				
	</tr>
	<?php  $row++; endforeach; ?>
	
</table>
                    
<?php if (!empty($records)): ?>
    <?php  include 'navline.php';?>
<?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<?php endif; ?>
</body>
</html>
