<?php
$page_title = "分析結果";
$active_nav = "分析";
include '../includes/header.php';
?>
    <div style="margin-bottom: 15px;">
      <label for="period_select" style="font-size: 14px;">期間選択</label>
      <select id="period_select" class="form-input" style="width: auto; display: inline-block; padding: 5px;">
        <option>今月</option>
        <option>先月</option>
      </select>
    </div>

    <div class="card">
        <div class="card-title" style="text-align: center;">総売上</div>
        <div class="card-content" style="text-align: center;">2,500,000円</div>
        <div class="card-sub-content" style="text-align: center;">前月比 +15%</div>
    </div>
    
    <div class="card" style="height: 100px; text-align: center; display: flex; align-items: center; justify-content: center;">
        <p class="card-sub-content">日別売上推移<br>(グラフ表示)</p>
    </div>
    
<?php
include '../includes/footer.php';
?>