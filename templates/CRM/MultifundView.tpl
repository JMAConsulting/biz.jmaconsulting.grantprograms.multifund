<div class="crm-accordion-wrapper" id='grant-multifund-entries'>
    <div class="crm-accordion-header">
      {ts}Multifund Entries{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <table class="crm-info-panel">
        <tr>
          <th>Funding Source</th>
          <th>Amount</th>
        </tr>
        {foreach from=$multiFundEntries key=name item=amount}
          <tr>
            <td class="label">{$name}</td>
            <td>{$amount|crmMoney}</td>
          </tr>
        {/foreach}
      </table>
  </div>
</div>

{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('.crm-info-panel:first').after($('#grant-multifund-entries'));
    });
  </script>
{/literal}
