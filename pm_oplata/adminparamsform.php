<?php defined('_JEXEC') or die(); ?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td  class="key">
     Merchant id
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[merchant_id]" size="45" value = "<?php echo $params['merchant_id']?>" />
     <?php echo JHTML::tooltip('Your merchant id in Oplata.com');?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Merchant password
   </td>
   <td>
       <input type = "text" class = "inputbox" name = "pm_params[merchant_salt]" size="45" value = "<?php echo $params['merchant_salt']?>" />
     <?php echo JHTML::tooltip('Your merchant password in Oplata.com'); ?>
   </td>
 </tr>

</table>
</fieldset>
</div>
<div class="clr"></div>