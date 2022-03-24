function runTabs(){
    document.querySelectorAll(".tabs-button").forEach(button => {  
        // create EventListener for everu tabs button
        button.addEventListener("click", () => { 
            // get parent element('tabs')
            const tabs = button.closest('.tabs') 
            // get current tab
            const tabNumber = button.dataset.forTab
            // get current tab content
            const tabToActivate = tabs.querySelector('.tabs-content[data-tab="'  + tabNumber  + '"]')
            // get if backround color is set
            const background = tabs.getAttribute('background')           
            /// reset .tabs-content
            reset(tabs,'.tabs-content-active', classList = ['tabs-content-active', background])
            // reset .tabs-button;
            reset(tabs, '.tabs-button', classList = ['tab-button-active', background])           
            // add class in tabs-content
            // tabs-content-active for active content
            tabToActivate.classList.add('tabs-content-active')
            //set background color for active content
            tabToActivate.classList.add(background)
            // add class in tabs-button
            // add class tab-button-active for active tab button           
            button.classList.add('tab-button-active') 
            // add background color for active button
            button.classList.add(background)          
        })
    })
}
// reset element 
// remove some classes (active, background)
function reset(parent,child,classList)
{    
    // find selector element in parent elemet
    // and iterate the child element
    parent.querySelectorAll(child).forEach(content => {
            // iterate the classes 
            classList.forEach(classitem => {               
                // if class is not null              
                if(classitem != null){
                    // remove class 
                    content.classList.remove(classitem);                    
                }               
            })
    });
}
// run automatically when the page run
document.addEventListener("DOMContentLoaded", () => {   
    runTabs();     
    document.querySelectorAll('.tabs').forEach(tabcontainer => {
        tabcontainer.querySelector('.tabs-button').click();
    });
});





