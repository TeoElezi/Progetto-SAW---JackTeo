<?php
include '../includes/header.php';
?>
<div class="container my-5">
    <h1 class="text-3xl font-bold mb-6">Piloti</h1>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
            $sql = "SELECT d.*, t.name AS team_name FROM drivers d LEFT JOIN teams t ON d.team_id = t.id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col">
                        <div class="card shadow-lg rounded-2xl overflow-hidden">
                            <?php if (!empty($row['image_url'])): ?>
                                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top object-cover h-56 w-full" alt="<?= htmlspecialchars($row['name']) ?>">
                            <?php else: ?>
                                <div class="bg-gray-300 h-56 flex items-center justify-center">
                                    <span class="text-gray-600">Nessuna immagine</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body bg-white p-4">
                                <h5 class="card-title text-xl font-semibold"><?= htmlspecialchars($row['name']) ?></h5>
                                <p class="text-sm text-gray-700 mb-1"><strong>Nazionalità:</strong> <?= htmlspecialchars($row['nationality']) ?></p>
                                <p class="text-sm text-gray-700 mb-1"><strong>Punti:</strong> <?= (int)$row['points'] ?></p>
                                <p class="text-sm text-gray-500"><strong>Team:</strong> <?= htmlspecialchars($row['team_name']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>Nessun pilota trovato.</p>";
            }
            $conn->close();
        ?>
    </div>
</div>
<?php
include '../includes/footer.php';
?>