<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate><?php echo $now; ?></responseDate>
    <request verb="Identify"><?php echo htmlentities($base); ?></request>
    <Identify>
        <repositoryName><?php echo $name; ?></repositoryName>
        <baseURL><?php echo $base; ?></baseURL>
        <protocolVersion>2.0</protocolVersion>
        <adminEmail><?php echo $email; ?></adminEmail>
        <earliestDatestamp>0000-01-01T00:00:00Z</earliestDatestamp>
        <deletedRecord>persistent</deletedRecord>
        <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
        <compression>deflate</compression>
    </Identify>
</OAI-PMH>