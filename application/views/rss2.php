<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom">

<channel>
    <title><?php echo $feed_title; ?></title>
    <atom:link href="<?php echo $feed_home; ?>" rel="self" type="application/rss+xml" />
    <link><?php echo $feed_base; ?></link>
    <description><?php echo $feed_description; ?></description>

<?php
    
   $titlefield = $this->skylight_utilities->getField($feed_fields['Title']);
   $authorfield = $this->skylight_utilities->getField($feed_fields['Author']);
   $subjectfield = $this->skylight_utilities->getField($feed_fields['Subject']);
   $descriptionfield = $this->skylight_utilities->getField($feed_fields['Description']);
   $datefield = $this->skylight_utilities->getField($feed_fields['Date']);


    foreach ($feed_items['recent_items'] as $item) {  ?>
    <item>
        <title><?php echo $item[$titlefield][0]; ?></title>
        <link><?php echo $feed_base . 'record/' . $item['id']; ?></link>
        <pubDate><?php echo $item[$datefield][0]; ?></pubDate>
        <?php if (isset($item[$authorfield])) foreach ($item[$authorfield] as $author) { ?>
            <dc:creator><?php echo $author; ?></dc:creator>
        <?php } ?>
        <?php if (isset($item[$subjectfield])) foreach ($item[$subjectfield] as $subject) { ?>
            <category><?php echo $subject; ?></category>
        <?php } ?>
        <description><?php if (isset($item[$descriptionfield])) foreach ($item[$descriptionfield] as $description) { ?>
            <?php echo $description; ?>
        <?php } ?></description>
    </item>
    <?php } ?>

</channel>

</rss>
