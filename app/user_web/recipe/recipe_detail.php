<?php
// サンプルデータ。本来はデータベースから取得します。
$all_recipes = [
    '1' => [
        'name' => 'ハンバーグ',
        'time' => '30分',
        'image' => '/assets/image/hamburg.png',
        'materials' => [
            '合いびき肉' => '300g',
            '玉ねぎ' => '1/2個',
            '卵' => '1個',
            'パン粉' => '大さじ5',
            '牛乳' => '大さじ3',
            '塩、こしょう' => '各少々',
        ],
        'steps' => [
            '玉ねぎをみじん切りにし、炒める。',
            'ボウルにひき肉、炒めた玉ねぎ、卵、パン粉、牛乳、塩こしょうを入れ、粘りが出るまでよく混ぜる。',
            '空気を抜きながら小判形に成形し、中央をくぼませる。',
            'フライパンに油を熱し、中火で焼き色がつくまで焼く。裏返して蓋をし、弱火で蒸し焼きにする。',
        ],
        'video' => 'https://via.placeholder.com/480x270.png?text=Sample+Video'
    ],
    // 他のレシピID（2〜6）も同様に定義...
    '7' => [
        'name' => '麻婆豆腐',
        'time' => '30m', // 画像から読み取り
        'image' => '/assets/image/mapo-tofu.png', // このパスに画像を配置してください
        'materials' => [
            '木綿豆腐' => '1丁',
            '豚ひき肉' => '100g',
            '長ねぎ' => '1/2本',
            'にんにく' => '1かけ',
            'しょうが' => '1かけ',
            'サラダ油' => '大さじ1',
            '豆板醤' => '小さじ1',
            'ごま油' => '適量',
            '細ねぎ(刻み)' => '適量',
        ],
        'steps' => [
            '豆腐は2cm角の角切りにする。ねぎ、にんにく、しょうがはみじん切りにする。',
            // ... 以降の手順を追加
        ],
        'video' => 'https://via.placeholder.com/480x270.png?text=Sample+Video' // サンプル動画
    ],
];

// URLからIDを取得
$recipe_id = $_GET['id'] ?? null;
$recipe = $all_recipes[$recipe_id] ?? null;

$page_title = $recipe ? htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8') : 'レシピが見つかりません';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/recipe/recipe-detail.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'; ?>

    <main>
        <?php if ($recipe): ?>
            <div class="recipe-detail-container">
                <div class="recipe-title-container">
                    <h1><?php echo htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <span class="recipe-time"><?php echo htmlspecialchars($recipe['time'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <img src="<?php echo htmlspecialchars($recipe['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8'); ?>" class="recipe-main-image">

                <div class="recipe-content-grid">
                    <div class="materials-section">
                        <h2>材料【2人分】</h2>
                        <ul>
                            <?php foreach ($recipe['materials'] as $name => $amount): ?>
                                <li><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?><span><?php echo htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="steps-section">
                        <div class="steps-header">
                            <h2>手順</h2>
                            <span class="arrow-icon">></span>
                        </div>
                        <div class="step-video-container">
                           <video controls poster="<?php echo htmlspecialchars($recipe['image'], ENT_QUOTES, 'UTF-8'); ?>">
                               <source src="<?php echo htmlspecialchars($recipe['video'], ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                               Your browser does not support the video tag.
                           </video>
                           <p>1. 豆腐は2cm角の角切りにする。ねぎ、にんにく、しょうがはみじん切りにする。</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="error-message">指定されたレシピは見つかりませんでした。</p>
        <?php endif; ?>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'; ?>

</body>
</html>