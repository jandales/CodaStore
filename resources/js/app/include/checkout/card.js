// import Cleave from  '/node_modules/cleave.js/dist/cleave-esm.min.js';
import Cleave from 'cleave.js';



// const cartNumberInput =  document.querySelector('.card-number');
// const MAX_LENGTH = 16;
// const DELIMITER = "-";
// if(cartNumberInput){
//     cartNumberInput.addEventListener('input', () => { 
//         const value = cartNumberInput.value;
//         let cardNumer = '';
     
//         if (value.length >= MAX_LENGTH){          
//             Array.from(value).forEach((number, index) => {  
//                if(index / 4 == 1 || index / 4 == 2 || index / 4 == 3){
//                     if(index < MAX_LENGTH) {
//                         cardNumer += ` ${DELIMITER} ${number}`; 
//                     }    
//                }  
//                else{                   
//                     if(index  < MAX_LENGTH) {
//                         cardNumer  += number; 
//                     }  
//                }     
              
//             });

          
           
//         }
//         cartNumberInput.value = cardNumer;
//     })
// }

var cleave = new Cleave('.card-number', {
    creditCard: true,
    delimiter: '-',
    onCreditCardTypeChanged: function (type) {
        console.log(type);
    }
});

var expiredDate = new Cleave('.expired-date', {
    date: true,
    datePattern: ['m', 'y'],
    delimiter: '/',
});