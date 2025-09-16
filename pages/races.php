<?php

function getRaces($conn) {
    $sql = "SELECT id, name, date, location, circuit_img FROM races ORDER BY date ASC";
    return $conn->query($sql);
}

include '../includes/header.php';

$racesResult = getRaces($conn);
$races = [];
if ($racesResult && $racesResult->num_rows > 0) {
    while ($row = $racesResult->fetch_assoc()) {
        $races[] = $row;
    }
}

$today = new DateTime('now');
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <h1 class="mb-3 mb-md-0" style="color:#c30000;">Gare di Formula 1</h1>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group" aria-label="Filtri">
                <button type="button" class="btn btn-outline-danger active" data-filter="all">Tutte</button>
                <button type="button" class="btn btn-outline-danger" data-filter="upcoming">Prossime</button>
                <button type="button" class="btn btn-outline-danger" data-filter="past">Passate</button>
            </div>
            <div class="ms-0 ms-md-2">
                <input id="raceSearch" type="search" class="form-control" placeholder="Cerca circuito o luogo..." aria-label="Cerca">
            </div>
        </div>
    </div>

    <?php if (empty($races)): ?>
        <div class="alert alert-info">Nessuna gara trovata.</div>
    <?php else: ?>
        <div id="racesGrid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($races as $race):
                $raceDate = new DateTime($race['date']);
                $isUpcoming = $raceDate >= $today;
                $badge = $isUpcoming ? '<span class="badge bg-success ms-2">Prossima</span>' : '<span class="badge bg-secondary ms-2">Passata</span>';
            ?>
            <div class="col" data-name="<?php echo htmlspecialchars(strtolower($race['name'] . ' ' . $race['location'])); ?>" data-type="<?php echo $isUpcoming ? 'upcoming' : 'past'; ?>">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($race['circuit_img'])): ?>
                        <img src="<?php echo htmlspecialchars($race['circuit_img']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($race['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span><?php echo htmlspecialchars($race['name']); ?></span>
                            <?php echo $badge; ?>
                        </h5>
                        <div class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($race['location']); ?>
                        </div>
                        <div class="mb-3">
                            <i class="fas fa-calendar-day me-2"></i><?php echo $raceDate->format('d M Y'); ?>
                        </div>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <a href="<?php echo getBasePath(); ?>pages/404.php" class="btn btn-outline-dark btn-sm">Dettagli</a>
                            <?php if ($isUpcoming): ?>
                                <span class="small text-muted">Manca poco al via</span>
                            <?php else: ?>
                                <span class="small text-muted">Evento concluso</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function(){
    const buttons = document.querySelectorAll('[data-filter]');
    const grid = document.getElementById('racesGrid');
    const cards = grid ? grid.querySelectorAll('.col') : [];
    const search = document.getElementById('raceSearch');
    let current = 'all';

    function apply() {
        const q = (search?.value || '').trim().toLowerCase();
        cards.forEach(card => {
            const type = card.getAttribute('data-type');
            const name = card.getAttribute('data-name');
            const matchType = (current === 'all') || (type === current);
            const matchText = q === '' || (name && name.indexOf(q) !== -1);
            card.style.display = (matchType && matchText) ? '' : 'none';
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            current = btn.getAttribute('data-filter');
            apply();
        });
    });

    search?.addEventListener('input', apply);
})();
</script>

<?php include '../includes/footer.php'; ?>