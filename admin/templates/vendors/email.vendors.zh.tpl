<html>
  <head></head>
  <body>

  <h3>腾达航勤订单 - {$vendor.company_name}</h3>

  <h4><a href="http://www.tld-gse.com/evendors/evendors.php?m[0]=po&m[1]=listing&m[2]=late">
  晚交货订单项数量 - {$stats.late}</a></h4>

  <p>点击订单号查看详情</p>

  <h4><a href="http://www.tld-gse.com/evendors/evendors.php?m[0]=po&m[1]=listing&m[2]=unconfirmed">
  未确认订单项 - {$stats.unconfirmed}</a></h4>

  <p>点击订单号查看详情</p>

  <p>请登录TLD供应商信息网站，确认预计交货日期 : 
  <a href="https://evendors.tld-gse.com">https://evendors.tld-gse.com</a></p>

  <p>您可以登录TLDTLD供应商信息网站查询所有的未交货订单和订单详情。
  如忘记密码，请联系您对应的TLD采购员<a href="mailto:{$vendor.rep_email}">{$vendor.rep_email}</a> ，
  或使用密码恢复系统</p>

  <p>您每周都会收到一封邮件来提醒您更新订单信息。如有任何疑问，请联系与您对应的TLD采购员 
  <a href="mailto:{$vendor.rep_email}">{$vendor.rep_email}</a>.</p>

  </body>
</html>