import './bootstrap';

import jQuery from 'jquery';
window.$ = jQuery;



$('.close-product').on('click', function(){
    $(this).parent().parent().remove()
})