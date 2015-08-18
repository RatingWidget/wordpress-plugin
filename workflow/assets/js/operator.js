function Operator() {
	var operators = {
		'contain': 'contains',
		'-contain': 'does not contain',
		'equal': 'is equal to',
		'-equal': 'is not equal to'
	}, operation = function() {
		var operator = arguments[0];
		var negate = false;
		if ( 0 === operator.indexOf('-') ) {
			negate = true;
			operator = operator.substring(1);
		}
		
		var operand1 = arguments[1];
		var operand2 = arguments[2];
		
		var evaluation = undefined;
		
		switch(operator) {
			case 'contain':
				return (-1 !== operand1.indexOf(operand2));
				break;
			case 'equal':
				return (operand1 === operand2);
				break;
		}
	}
}