

    export default  function generate(){
        const PASSWORD_LENGTH = 24;
        const COMBINATIONS = ['NUMBERS', 'ALPHABET', 'SYMBOLS']
        const PASSWORD_COMBATIONS = { 
            ALPHABET : ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
            NUMBERS : ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            SYMBOLS : ['!', '#', '$', '%', '&', '(', ')', '*', '+'],   
        }   
       
        let length = 0;
        let password = '';    
        while (length < PASSWORD_LENGTH) {   
            let combinationIndex = Math.floor(Math.random() * COMBINATIONS.length)      
            let key = COMBINATIONS[combinationIndex]
            let index = Math.floor(Math.random() * PASSWORD_COMBATIONS[key].length)
            password += PASSWORD_COMBATIONS[key][index];
            length++       
        }
    
        return password;
    } 
    



