<!DOCTYPE html>
<html lang="en">
<head>
    <title>Test Form</title>
    <style>
        li {
            list-style-type: none;
        }
    </style>
</head>
<body>
<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the answers and calculate the score
    // Store the score in a session
    session_start();
    $score = $_POST['score'] ?? 0;
    $_SESSION['score'] = $score;

    // Check if all questions were answered
    //$questions = explode(',', $_POST['questions'] ?? '');

    //$numQuestions = count($questions);
    $numCorrectAnswers = 0;

    // Checking if $questions is an array before using the count() function
    //if (is_array($questions)) {
    // Calculate the number of correct answers on this page
    //$questions_pageData = file('questions_page' . $_POST['page'] . '.txt', FILE_IGNORE_NEW_LINES);

    // Чтение содержимого файла
    $file_name = 'questions_page' . $_POST['page'] . '.txt';

    $questions_and_answers = array();

    // Чтение содержимого файла
    $file_content = file_get_contents($file_name);

    // Разделение содержимого файла на строки
    $lines = explode(PHP_EOL, $file_content);

    // Итерация по строкам файла
    for ($i = 0; $i < count($lines); $i += 6) {
        // Извлечение вопроса
        $question = trim($lines[$i]);

        // Извлечение правильного ответа
        $correct_answer_line = trim($lines[$i + 5]);
        $correct_answer = substr($correct_answer_line, strlen("Correct Answer: "));

        // Добавление вопроса и ответа в ассоциативный массив
        $questions_and_answers[$question] = $correct_answer;
    }

    // Add the current page's score to the overall score
    $_SESSION['score'] += ($numCorrectAnswers * ($_POST['page'] == 1 ? 1 : 3));

    // Proceed to the next page or show the result if it's the last page
    if ($_POST['page'] == 3) {
        header("Location: result.php");
        exit;
    } else {
        header("Location: test_form.php?page=" . ($_POST['page'] + 1));
        exit;
    }
}

// Check if we need to start the test again
if (isset($_GET['restart']) && $_GET['restart'] == 1) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: test_form.php");
    exit;
}

// Get the page number from the URL query parameter or set it to 1
$currentPage = isset($_GET['page']) ? max(1, min(3, intval($_GET['page']))) : 1;

// Read the questions for the current page from the file and shuffle them
$questionsContent = file_get_contents('questions_page' . $currentPage . '.txt');
$questionsArr = explode("\r\n\r\n", $questionsContent);

shuffle($questionsArr);
?>

<form method="post" onsubmit="return validateForm(<?php echo $currentPage; ?>)">
    <h1>Test Form - Page <?php echo $currentPage; ?></h1>
    <?php
    $questionNumber = 1;

    // Массив для хранения вопросов и выбранных ответов
    $questionsWithAnswers = [];

    foreach ($questionsArr as $question) {
        if (trim($question) === '') {
            continue;
        }

        $lines = explode("\n", $question);
        $questionText = trim($lines[0]);
        // Добавить все вопросы в массив со значениями по умолчанию
        $questionsWithAnswers[$questionText] = null;
        $options = array_slice($lines, 1);

        // Skip the question if it contains "Correct Answer"
        if (strpos($questionText, 'Correct Answer') !== false) {
            continue;
        }

        echo "<div>";
        echo "<p>{$questionText}</p>";
        echo "<ul>";
        foreach ($options as $option) {
            // Skip lines that contain "Correct Answer"
            if (strpos($option, 'Correct Answer') !== false) {
                if ($currentPage == 3) {
                    echo "<input type='text' name='question_{$questionNumber}[]' value=''>";
                }
                continue;
            }

            $optionText = trim($option);
            $optionLetter = substr($option, 0, strpos($option, ')') + 1);

            if ($currentPage == 1)
                echo "<li><label><input type='radio' name='question_{$questionNumber}[]' value='{$optionLetter}'> {$optionText}</label></li>";
            if ($currentPage == 2)
                echo "<li><label><input type='checkbox' name='question_{$questionNumber}[]' value='{$optionLetter}'> {$optionText}</label></li>";
//            if ($currentPage == 3) {
//                echo "<div>";
//                echo "<p>Enter your answers:</p>";
//                echo "<ul>";
//                foreach ($options as $option) {
//                    // Skip lines that contain "Correct Answer"
//                    if (strpos($option, 'Correct Answer') !== false) {
//                        continue;
//                    }
//
//                    $optionText = trim($option);
//                    $optionLetter = substr($option, 0, strpos($option, ')') + 1);
//
//                    echo "<li><label><input type='text' name='question_{$questionNumber}[]' value='{$optionLetter}'> {$optionText}</label></li>";
//                }
//                echo "</ul>";
//                echo "</div>";
//            }
        }
        echo "</ul>";
        echo "</div>";

        $questionNumber++;

        // Если это страница с радио-кнопками или чекбоксами (страницы 1 и 2)
        if ($currentPage == 1 || $currentPage == 2) {
            // Получить выбранный ответ для текущего вопроса
            $selectedOption = $_POST["question_{$questionNumber}"][0] ?? null;
            // Сохранить вопрос и выбранный ответ в массив
            $questionsWithAnswers[$questionText] = $selectedOption;
        } else if ($currentPage == 3) {
            // Если это страница с полем ввода (страница 3)
            // Получить все введенные ответы для текущего вопроса
            $selectedOptions = $_POST["question_{$questionNumber}"] ?? [];
            // Преобразовать введенные ответы в строку, разделяя их запятыми
            $selectedAnswer = implode(', ', $selectedOptions);
            // Сохранить вопрос и введенные ответы в массив
            $questionsWithAnswers[$questionText] = $selectedAnswer;
        }
    }

    ?>
    <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
    <button type="submit">Next</button>
</form>

<script>
    function validateForm(page) {
        // Check if all questions on the first page were answered before proceeding to the next page
        //if (page === 1) {
        //    // Получить все радио-кнопки на странице
        //    const inputs = document.querySelectorAll('input[type="radio"]');
        //    // Подсчитать количество выбранных радио-кнопок
        //    let numChecked = 0;
        //    for (const input of inputs) {
        //        if (input.checked) {
        //            numChecked++;
        //        }
        //    }
        //
        //    // Если количество выбранных радио-кнопок не равно количеству вопросов на странице, то вывести сообщение об ошибке
        //    if (numChecked !== <?php //echo count($questionsArr); ?>//) {
        //        alert('Please answer all questions before proceeding to the next page.');
        //        return false;
        //    }
        //}

        return true;
    }
</script>

</body>
</html>