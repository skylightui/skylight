<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?php echo $now; ?></responseDate>
  <request verb="ListSets"><?php echo htmlentities($base); ?></request>
  <error code="noSetHierarchy">This repository does not support sets</error>
</OAI-PMH>
