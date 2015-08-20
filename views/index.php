<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Test</title>
</head>
<body>
<h1>Entries</h1>
<ul>
    <?php if(!empty($entries)):?>
    <?php foreach($entries as $key => $entry):?>
    <li><a href="/index.php?r=update&id=<?= $entry['id'];?>"><?=$entry['title']?></a> <a href="/index.php?r=delete&id=<?=$key;?>">x</a></li>
    <?php endforeach;?>
    <?php endif;?>
</ul>
<?php if(empty($data)):?>
    <h2> Add new entry</h2>
    <form method="post" action="/index.php?r=add" id="myform">
        <div>
            <label for ="id">id</label>
            <input type="text" name ="form[id]"  />
        </div>
        <div>
            <label for ="id">name</label>
            <input type="text" name ="form[name]"  />
        </div>
        <div>
            <label for ="id">title</label>
            <input type="text" name ="form[title]"  />
        </div>
        <div>
            <input type="submit" value="Add">
        </div>
    </form>
<?php else:?>
    <h2> Edit entry entry</h2>
    <form method="post" action="/index.php?r=update&id=<?= $data['id'];?>" id="myform1">
        <div>
            Entry id  <?=  $data['id'];?>
        </div>
        <div>
            <label for ="id">name</label>
            <input type="text" name ="form[name]" value="<?= $data['name'];?>" />
        </div>
        <div>
            <label for ="id">title</label>
            <input type="text" name ="form[title]"  value="<?= $data['title'];?>" />
        </div>
        <div>
            <input type="submit" value="Update">
        </div>
    </form>
<?php endif;?>
</body>
</html>
