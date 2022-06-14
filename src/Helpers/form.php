<?php declare(strict_types=1);

isset($attach) || exit('Failed to populate the form!'); ?>

<!-- attach the params -->
<form method="POST" action="<?php echo $attach->url; ?>" id="esewa_form">
    <input name="scd" type="hidden" value="<?php echo $attach->merchantCode; ?>">
    <input name="su" type="hidden" value="<?php echo $attach->successUrl; ?>">
    <input name="fu" type="hidden" value="<?php echo $attach->failureUrl; ?>">
    <input name="amt" type="hidden" value="<?php echo $attach->amount; ?>">
    <input name="txAmt" type="hidden" value="<?php echo $attach->taxAmount; ?>">
    <input name="psc" type="hidden" value="<?php echo $attach->serviceAmount; ?>">
    <input name="pdc" type="hidden" value="<?php echo $attach->deliveryAmount; ?>">
    <input name="tAmt" type="hidden" value="<?php echo $attach->totalAmount; ?>">
    <input name="pid" type="hidden" value="<?php echo $attach->productId; ?>">
</form>
<!-- auto submit the form -->
<script type="text/javascript">document.getElementById('esewa_form').submit();</script>
