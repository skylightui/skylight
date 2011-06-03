<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate><?php echo $now; ?></responseDate>
    <request><?php echo htmlentities($url); ?></request>
    <error code="badVerb">Illegal verb</error>
</OAI-PMH>