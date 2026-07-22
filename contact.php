<?php

require_once __DIR__ . '/bootstrap/web.php';

(new GuideMyPC\Features\Pages\PageController(new GuideMyPC\Core\View()))->contact();
