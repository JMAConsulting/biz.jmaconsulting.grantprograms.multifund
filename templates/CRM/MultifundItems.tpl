<div id='multi-fund'>
  {section name='i' start=0 loop=$totalCount}
    {assign var='rowNumber' value=$smarty.section.i.index}
    <div id="add-item-row-{$rowNumber}" class="hiddenElement">
      <span>{ts}Source of Funds{/ts}&nbsp;&nbsp;&nbsp;</span>
      <span>{$form.financial_account.$rowNumber.html}</span>
      <span>{$form.multifund_amount.$rowNumber.html}</span>
      <span><a href=# class="remove_item crm-hover-button" title='Cancel'><i class="crm-i fa-times"></i></a></span>
    </div>
  {/section}
  <a href=# id='add-item' class="action-item crm-hover-button">{ts}Add addional source of funds line{/ts}</a>
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  var submittedRows = $.parseJSON('{/literal}{$itemSubmitted}{literal}');
  $.each(submittedRows, function(e, num) {
    isSubmitted = true;
    $('#add-item-row-' + num).removeClass('hiddenElement');
  });

  $('#financial_type_id').after($('#multi-fund'));
  $('#add-item').on('click', function() {
    if ($('div.hiddenElement:first').hasClass("hiddenElement")) {
      $('div.hiddenElement:first').show().removeClass('hiddenElement');
    }
    else {
      $('#add-item').hide();
    }
  });
  $('.remove_item').on('click', function() {
    var row = $(this).closest('div');
    $('select[id^="financial_account"]', row).val('');
    $('input[id^="multifund_amount"]', row).val('');
    row.addClass('hiddenElement').hide();
    $('#add-item').show();
  });

  $('#add-item').toggle(($("#status_id option:selected").text() != 'Eligible'));
  $('#status_id').on('change', function() {
    $('#add-item').toggle(($("#status_id option:selected").text() != 'Eligible'));
  });
});
</script>
{/literal}
