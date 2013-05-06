<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eduard
 * Date: 30.04.13
 * Time: 13:26
 * To change this template use File | Settings | File Templates.
 */
?>
    <form name="merge_associates" method="post" action="<?php echo base_url()?>cases/merge_associates/<?php echo $case_id; ?>">
    <input type="hidden" name="not_active_associates" value="<?php echo $not_active_associates; ?>" />
        <select name="main_associate">
            <?php foreach($merge_associates as $associate){?>
            <option value="<?php echo $associate['associate_id'];?>"><?php echo $associate['name'].'('.$associate['country'].')';?></option>
    <?php }?>
        </select><br/><br/>
    <input type="submit" value="Merge" name="submit"/>
    </form>
