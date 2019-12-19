function displayPayment(payment_type, payment_param, itemid) {
	switch (payment_type) {
		case 0:
			var paymentParams='&ptype=pack&ad_id=' + payment_param;
			if (document.getElementById('buypack_1').checked) {
				paymentParams+='&pack1=1';
				paymentParams+='&pack1weeks=' + document.getElementById('pack1weeks').value;
			}
			if (document.getElementById('buypack_2').checked) {
				paymentParams+='&pack2=1';
			}
			if (itemid > 0) {
				paymentParams += '&Itemid=' + itemid;
			}
			//paymentParams += '&tmpl=component';
			break;
		case 1:
			var paymentParams='&ptype=auth&phones=' + payment_param;
			break;
	}
	if (itemid > 0) {
		paymentParams += '&Itemid=' + itemid;
	}
	var url='index.php?option=com_wfxcomponent&view=store&task=payment' + paymentParams;
	return url;
}