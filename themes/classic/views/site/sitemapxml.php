<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach($posts as $model): ?>
    <url>
        <loc><?php echo CHtml::encode($this->createAbsoluteUrl('post/show',array('slug'=>$model->slug))); ?></loc>        <changefreq>weekly</changefreq>        <priority>0.5</priority>    </url>
<?php endforeach; ?>

<?php foreach($pages as $model): ?>
    <url>
        <loc><?php echo CHtml::encode($this->createAbsoluteUrl('page/show',array('slug'=>$model->slug))); ?></loc>        <changefreq>weekly</changefreq>        <priority>0.5</priority>    </url>
<?php endforeach; ?>

</urlset>
