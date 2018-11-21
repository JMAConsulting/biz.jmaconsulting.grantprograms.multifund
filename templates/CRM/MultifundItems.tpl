<div id='multi-fund' style="float:right;">
  <div id='multi-fund-entries' class="hiddenElement">
    {section name='i' start=0 loop=$totalCount}
      {assign var='rowNumber' value=$smarty.section.i.index}
      <div id="add-item-row-{$rowNumber}">
        <span>{$form.financial_account.$rowNumber.html}</span>
        <span>{$form.multifund_amount.$rowNumber.html}</span>
        <span><a href=# class="remove_item crm-hover-button" title='Cancel'><i class="crm-i fa-times"></i></a></span>
      </div>
    {/section}
  </div>
  <a href=# id='add-item' class="action-item crm-hover-button">{ts}Add additional source of funds line{/ts}</a>
</div>
<div id='multi-fund-help' class="description hiddenElement">
  <br/>
  <br/>
  {ts}When Multiple Sources of Funds are specified, the Financial Type is used only to determined the <br/>financial accounts for Accounts Payable and Bank Account.{/ts}
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  var submittedRows = $.parseJSON('{/literal}{$itemSubmitted}{literal}');
  var isSubmitted = false;
  $.each(submittedRows, function(e, num) {
    isSubmitted = true;
    $('#add-item-row-' + num).removeClass('hiddenElement');
  });

  if ($('#financial_type_id').length) {
    $('#financial_type_id').after($('#multi-fund'));
    $('#multi-fund').after($('#multi-fund-help'));
  }

  $('#add-item').on('click', function() {
    $('#multi-fund-entries').show().removeClass('hiddenElement');
    $.each($('#multi-fund-entries div'), function() {
      var row = $(this);
      if (row.hasClass('hiddenElement')) {
        row.show().removeClass('hiddenElement');
      }
    });
    $('#add-item').hide();
    $('#multi-fund-help').removeClass('hiddenElement');
  });
  $('.remove_item').on('click', function() {
    var row = $(this).closest('div');
    $('select[id^="financial_account"]', row).val('');
    $('input[id^="multifund_amount"]', row).val('');
    row.addClass('hiddenElement').hide();
    $('#add-item').show();
    if ($('#multi-fund-entries div.hiddenElement').length == 2) {
      $('#multi-fund-help').addClass('hiddenElement');
    }
  });

  $('#add-item').toggle(($("#status_id option:selected").text() != 'Eligible'));
  $('#status_id').on('change', function() {
    $('#add-item').toggle(($("#status_id option:selected").text() != 'Eligible'));
  });

  if (isSubmitted) {
    $('#multi-fund-entries, #multi-fund-help').show().removeClass('hiddenElement');
    $('#add-item').addClass('hiddenElement');
  }
});
</script>
{/literal}
