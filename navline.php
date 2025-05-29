<?php
// Kräver att följande variabler är tillgängliga:
// $isFiltered, $rangeStart, $rangeEnd, $foundCount, $page, $offset, $limit, $queries, $showBackLink
$showBackLink = !empty($queries) && ($_GET['origin'] ?? '') !== 'recordlist';
$totalPages = max(1, ceil($foundCount / $limit));
?>
<div class="form-row nav-line">
	<?php if ($showBackLink): ?>
		<a href="findrecords.php?back=1&<?= http_build_query($_GET) ?>" class="nav-button">← Tillbaka</a>
	<?php endif; ?>

    <?php if ($totalPages > 5): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="nav-button">Start</a>
    <?php endif; ?>

    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="nav-button">&laquo; Föregående</a>
    <?php endif; ?>

    <span class="info-text">
        <?= $isFiltered
            ? "Visar {$rangeStart}–{$rangeEnd} av {$foundCount} träffar"
            : "Visar {$rangeStart}–{$rangeEnd} av {$foundCount} poster"; ?>
    </span>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="nav-button">Nästa &raquo;</a>
    <?php endif; ?>

    <?php if ($totalPages > 5): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" class="nav-button">Slut</a>
    <?php endif; ?>
</div>