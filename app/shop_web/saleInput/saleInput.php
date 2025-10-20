<?php
$page_title = "セール情報入力";
$active_nav = "入力";
include '../includes/header.php';
?>
    <form action="" method="post" id="saleEntryForm">
        <div class="form-group">
            <label for="product_name">商品名 <span style="color: red;">*</span></label>
            <input type="text" id="product_name" name="product_name" required class="form-input">
        </div>

        <div class="form-group">
            <label for="category">カテゴリ <span style="color: red;">*</span></label>
            <input type="text" id="category" name="category" required class="form-input">
        </div>
        
        <div class="form-group">
            <label for="normal_price">通常価格 <span style="color: red;">*</span></label>
            <input type="number" id="normal_price" name="normal_price" required class="form-input">
        </div>
        
        <div class="form-group">
            <label for="notes">特記事項</label>
            <textarea id="notes" name="notes" class="form-input"></textarea>
        </div>

        <button type="submit" class="submit-button">この内容で登録する</button>
    </form>
    
<?php
include '../includes/footer.php';
?>