<ul  class="rate-stars">
    @for ($j = 0; $j < $rating; $j++)
         <li><i class="fas fa-star" aria-hidden="true"></i><li>  
    @endfor
    @for ($i = 0; $i < 5 - $rating; $i++)                      
         <li><i class="far fa-star" aria-hidden="true"></i><li>  
    @endfor
</ul>