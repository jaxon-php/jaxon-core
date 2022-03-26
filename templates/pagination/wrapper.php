<ul class="pagination"><?php
    if(($this->prev))
    {
        echo $this->prev;
    }
    foreach($this->links as $link)
    {
        echo $link;
    }
    if(($this->next))
    {
        echo $this->next;
    }
?></ul>
