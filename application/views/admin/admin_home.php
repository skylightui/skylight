<div class="content">
    <p>

        <div class="section">

            <ul>
                <li><strong><a href="./admin/displayconfig">View the configuration</a></strong></li>
                <li><strong>Edit static content pages:</strong>
                    <ul>
                        <?php
                            // List each static page
                            $local_path = $this->config->item('skylight_local_path');
                            if (file_exists('../skylight-local/static/' . $theme)) {
                                $handle = opendir('../skylight-local/static/' . $theme);
                            } else if (file_exists($local_path . '/static/' . $theme)) {
                                $handle = opendir($local_path . '/static/' . $theme);
                            } else {
                                $handle = false;
                            }

                            while (($handle != false) && (false !== ($file = readdir($handle)))) {
                                if (!preg_match("/^\./", $file)) {
                                    if (is_dir($local_path . '/static/' . $theme . '/' . $file)) {
                                        ?><li><?php echo $file . '/'; ?><ul><?php
                                        $handle2 = opendir($local_path . '/static/' . $theme . '/' . $file);
                                        while (false !== ($file2 = readdir($handle2))) {
                                            if (!preg_match("/^\./", $file2)) {
                                                $file2 = substr($file2, 0, strlen($file2) - 4);
                                                ?><li>
                                                    <a href="<?php echo './admin/content?mode=edit&file=' . $file . '/' . $file2; ?>"><?php echo $file2; ?></a>
                                                </li><?php
                                            }
                                        }
                                        ?></ul></li><?php
                                    } else {
                                        $file = substr($file, 0, strlen($file) - 4);
                                        ?><li>
                                            <a href="<?php echo './admin/content?mode=edit&file=' . $file; ?>"><?php echo $file; ?></a>
                                        </li><?php
                                    }
                                }
                            }
                        ?>
                    </ul></li>
                <li><strong><a href="./admin/logout">Logout of the administrative interface</a></strong></li>
            </ul>

        </div>

    </p>
</div>