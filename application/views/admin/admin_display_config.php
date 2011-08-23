<div class="content">
    <p>
    <h3>Skylight Configuration</h3>
    <p><em>Note: To change your local configuration options, edit the 'application/config/skylight.php' file by hand.</em></p>
        <table class="config">
            <?php
                $i = 0;
        foreach ($config_array as $key => $value) {
                if(preg_match('/^skylight_/',$key)) {
                    $i++;
                    if($i % 2 == 0) {
                        $rowclass="even";
                    }
                    else {
                        $rowclass="odd";
                    }
            ?>
            <tr class="<?php echo $rowclass; ?>"><td class="configfield"><?php echo $key; ?></td><td class="configvalue"><?php
                if(is_array($value)) {
                    ?> <table class="config"> <?php
                    foreach($value as $key => $value) {
                        ?> <tr><td class="configfield"><?php echo $key ?></td><td class="configvalue"><?php echo $value ?></td>
                                <?php
                    }
                        ?> </table> <?php
                }

                else {
                    echo $value;
                }
             ?></td></tr>

        <?php }
            }
                ?>
        </table>
    </p>
</div>