'use strict';

//scroll
{   
    const header = document.querySelector('header');
    window.addEventListener('scroll', e => {
        const wScroll = window.pageYOffset;
        if(wScroll > 20){
            header.classList.add('header-active');
        } else{
            header.classList.remove('header-active');
        }
    });
}

// Product card
{
    const productCard = document.querySelectorAll('.product-card');

    productCard.forEach(e => {
        e.addEventListener('mouseover', () => {
            e.classList.add('product-card_active');
        });
    
        e.addEventListener('mouseout', () => {
            e.classList.remove('product-card_active');
        });
    });
}