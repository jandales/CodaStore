export function moneyFormatter(number){
    
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
    });

    return formatter.format(number);

}