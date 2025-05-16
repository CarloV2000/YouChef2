<main class="main">
    <?php
    error_reporting(0);
    ini_set('display_errors', 0);
    ?>

    <?php
    include 'db.php';

    $recipes = [];
    $passaggi = [];
    $currentStep = 0;

    // Gestione selezione categoria
    if (isset($_POST['category'])) {
        $categoria = $_POST['category'];
        $query = "SELECT * FROM recipes WHERE categoria = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$categoria]);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Gestione selezione ricetta
    if (isset($_POST['nome'])) {
        $query = "SELECT elencoPassaggi FROM recipes WHERE nome = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['nome']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $elencoPassaggi = $result['elencoPassaggi'];
            $passaggi = explode("- ", trim($elencoPassaggi));
        }
        $currentStep = $_POST['step'] ?? 0;
    }
    ?>

    <h2>Selezionare la categoria di ricetta</h2>

    <!-- MOSTRA I BOTTONI SOLO SE NON CI SONO PASSAGGI DA MOSTRARE -->
    <?php if (empty($passaggi)): ?>
    <form method="post">
        <div class="buttons">
            <button name="category" value="primo">Primo</button>
            <button name="category" value="secondo">Secondo</button>
            <button name="category" value="dolce">Dolce</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!empty($recipes) && empty($passaggi)): ?>
        <h3>Ricette - <?php echo ucfirst(htmlspecialchars($categoria)); ?></h3>
        <ul>
            <?php foreach ($recipes as $recipe): ?>
                <li>
                    <form method="post">
                        <input type="hidden" name="nome" value="<?php echo htmlspecialchars($recipe['nome']); ?>">
                        <button type="submit">
                            <?php echo htmlspecialchars($recipe['nome']) . " (" . htmlspecialchars($recipe['tempoStimato']) . " minuti)"; ?>
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (!empty($passaggi)): ?>
        <?php
        if ($currentStep < count($passaggi)) {
            $passaggio = $passaggi[$currentStep];
            preg_match("/(.*)\((\d+),(\d+),(\d+)\)/", $passaggio, $matches);
            $descrizione = $matches[1] ?? 'Passaggio non disponibile';
            $timer = $matches[2] ?? 0;
            $tempMin = $matches[3] ?? 0;
            $tempMax = $matches[4] ?? 0;
        ?>
        <h3>Passaggio <?php echo $currentStep + 1; ?> di <?php echo count($passaggi); ?></h3>
        <p><?php echo htmlspecialchars($descrizione); ?></p>
        <p>Timer: <?php echo $timer; ?> minuti</p>
        <p>Temperatura: <?php echo $tempMin; ?> - <?php echo $tempMax; ?> °C</p>

        <form method="post">
            <input type="hidden" name="nome" value="<?php echo htmlspecialchars($_POST['nome']); ?>">
            <input type="hidden" name="step" value="<?php echo $currentStep + 1; ?>">
            <button type="submit">Procedi</button>
        </form>

        <?php } else { ?>
            <h3>Ricetta completata!</h3>
            <form method="post">
                <button type="submit">Torna alla selezione</button>
            </form>
        <?php } ?>
    <?php endif; ?>
</main>
