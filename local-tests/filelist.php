<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../src/autoload.php');

if (!isset($GLOBALS['paths'])) {
    $GLOBALS['paths'] = [];
}

function getAllFiles($path)
{
    $result = [];
    if (is_dir($path)) {
        $directory = dir($path);

        $directory->read();
        $directory->read();

        while (false !== ($entry = $directory->read())) {
            $currentPath = $path;
            if (is_dir($path . DIRECTORY_SEPARATOR . $entry)) {
                $currentPath = $path . DIRECTORY_SEPARATOR . $entry;
                $content = getAllFiles($currentPath);
            } else {
                $content = $entry;
            }
            $result[realpath($currentPath)][] = $content;
        }
    }

    return $result;
}

$files = [];
foreach ($GLOBALS['paths'] as $path) {
    if (is_dir($path)) {
        $files[$path][] = getAllFiles($path);
    }
}

function printFiles($files)
{
    echo '<ul>';
    foreach ($files as $path => $resultingFiles) {
        foreach ($resultingFiles as $file) {
            if (is_array($file)) {
                echo '<li data-arrow="false">';
                echo '<p class="clickable">' . $path . '</p>';
                printFiles($file);
                echo '</li>';
            } else {
                $link = $path . DIRECTORY_SEPARATOR . $file;
                echo '<li>';
                echo '<input name="f[]" value="' . $link . '" type="checkbox">';
                echo '<a href="' . parse_url($_SERVER['REQUEST_URI'])['path'] . '?f=' . urlencode($link) . '">' . $file . '</a>';
                echo '</input>';
                echo '</li>';
            }
        }
    }
    echo '</ul>';
}

if (!isset($_GET['f'])):
    ?>

    <html>
    <head>
        <script type="text/javascript">
            window.addEventListener(
                'load',
                function () {
                    document.body.addEventListener(
                        'click',
                        function (event) {
                            var target = event.target;
                            if (target.classList.contains('clickable')) {
                                var parentNode = target.parentNode;
                                parentNode.dataset.arrow = !(parentNode.dataset.arrow === 'true')
                            }
                        }
                    )
                }
            );
        </script>

        <style>
            * {
                -webkit-user-select: none; /* Chrome/Safari */
                -moz-user-select: none; /* Firefox */
                -ms-user-select: none; /* IE10+ */

                /* Rules below not implemented in browsers yet */
                -o-user-select: none;
                user-select: none;
            }

            .clickable {
                cursor: pointer;
            }

            li[data-arrow='true'] {
                list-style-type: '↠';
            }

            li[data-arrow='true'] ul > * {
                display: none;
            }

            li[data-arrow='false'] {
                list-style-type: '↡';
            }
        </style>
    </head>
    <body>
    <form>
        <button type="submit">Send data</button>
        <?php printFiles($files); ?>
        <button type="submit">Send data</button>
    </form>
    </body>
    </html>
    <?php die();endif; ?>