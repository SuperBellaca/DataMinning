<?php
$questions = [
    [
        'question' => '¿Qué técnica de Orange se utiliza para visualizar la distribución de datos numéricos?',
        'options' => ['Scatter Plot', 'Box Plot', 'Heat Map', 'Tree View'],
        'correct' => 1
    ],
    [
        'question' => '¿Qué widget de Orange se usa para cargar datos desde archivos CSV o Excel?',
        'options' => ['Data Sampler', 'File', 'Data Table', 'Select Columns'],
        'correct' => 1
    ],
    [
        'question' => '¿Qué algoritmo de clasificación en Orange utiliza árboles de decisión?',
        'options' => ['Naive Bayes', 'Logistic Regression', 'Random Forest', 'kNN'],
        'correct' => 2
    ],
    [
        'question' => '¿Qué técnica de Orange se utiliza para reducir la dimensionalidad de los datos?',
        'options' => ['PCA', 'k-Means', 'SVM', 'Linear Regression'],
        'correct' => 0
    ],
    [
        'question' => '¿Qué widget de Orange se utiliza para evaluar el rendimiento de los modelos de clasificación?',
        'options' => ['Confusion Matrix', 'Scatter Plot', 'Box Plot', 'Test & Score'],
        'correct' => 3
    ],
    [
        'question' => '¿Qué significa PCA en minería de datos?',
        'options' => ['Principal Component Analysis', 'Primary Classification Algorithm', 'Plot Comparison Algorithm', 'Proportional Component Adjustment'],
        'correct' => 0
    ],
    [
        'question' => '¿Qué widget de Orange se usa para agrupar datos?',
        'options' => ['k-Means', 'PCA', 'Data Table', 'Heat Map'],
        'correct' => 0
    ],
    [
        'question' => '¿Cuál de estas técnicas es supervisada?',
        'options' => ['Naive Bayes', 'k-Means', 'PCA', 'Heat Map'],
        'correct' => 0
    ]
];

$score = isset($_POST['score']) ? intval($_POST['score']) : 0;
$total_questions = count($questions);
$current_question = isset($_POST['current_question']) ? intval($_POST['current_question']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['back'])) {
        $current_question = max(0, $current_question - 1);
    } elseif (isset($_POST['answer'])) {
        $selected_answer = intval($_POST['answer']);
        if ($selected_answer === $questions[$current_question]['correct']) {
            $score++;
        }
        $current_question++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Interactivo</title>
    <link rel="stylesheet" href="../quizz/css/style.css">
</head>
<body>
    <img src="css/santo_tomas.jpeg" class="logo" alt="Logo ST">
    <img src="css/orange.jpg" class="header-image" alt="Orange Logo">

    <!-- Botón Volver a la página principal -->
    <a href="../frontend/modulo.html" class="boton-volver"> <strong>Volver a la pagina principal</strong> </a>

    <div class="quiz-container">
        <h1>Quiz de Minería de Datos con Orange</h1>

        <?php if ($current_question < $total_questions): ?>
        <div class="question"><?php echo $questions[$current_question]['question']; ?></div>
        <div class="options">
            <?php foreach ($questions[$current_question]['options'] as $index => $option): ?>
                <button 
                    class="option" 
                    onclick="validateAnswer(this, <?php echo $index; ?>, <?php echo $questions[$current_question]['correct']; ?>)">
                    <?php echo $option; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo ($current_question / $total_questions) * 100; ?>%;"></div>
        </div>
        <form method="post" id="quiz-form">
            <input type="hidden" name="current_question" value="<?php echo $current_question; ?>">
            <input type="hidden" name="score" id="score" value="<?php echo $score; ?>">
            <input type="hidden" name="answer" id="answer">
            <div class="button-container">
                <?php if ($current_question > 0): ?>
                <button type="submit" name="back" class="button">Volver</button>
                <?php endif; ?>
            </div>
        </form>
        <?php else: ?>
        <div class="result">
            ¡Has completado el quiz!<br>
            Tu puntuación es: <?php echo $score; ?> de <?php echo $total_questions; ?>
        </div>
        <div class="button-container">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button">Volver a Intentar</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
    <script>
        function validateAnswer(button, selected, correct) {
            const buttons = document.querySelectorAll('.option');
            buttons.forEach((btn, index) => {
                if (index === correct) {
                    btn.classList.add('correct');
                } else {
                    btn.classList.add('incorrect');
                }
                btn.disabled = true;
            });

            document.getElementById('answer').value = selected;
            setTimeout(() => {
                document.getElementById('quiz-form').submit();
            }, 1500);
        }

        // Confeti al final del quiz
        <?php if ($current_question == $total_questions): ?>
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { x: 0.5, y: 0.5 }
            });
        <?php endif; ?>
    </script>

    <!-- Incluir Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>



