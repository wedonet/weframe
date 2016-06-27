<?php

function crumb($s='') {
    ?>
    <div class="crumb">
        您现在的位置：
        <ul>
            <?php
            echo $s;
            ?>
        </ul>
        <div class="fright prepage">
            <a href="javascript:void(0)" onclick="window.location.reload()">刷新</a> &nbsp; 
            <a href="javascript:history.go(-1);">&lt;&lt; 返回上一页</a>
        </div>
        <div class="clear"></div>
    </div>
    <?php
}