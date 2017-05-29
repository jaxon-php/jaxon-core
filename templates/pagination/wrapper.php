<ul class="pagination">
<?php
    if(($this->prev))
    {
        echo $this->prev;
    }
    echo $this->links;
    if(($this->next))
    {
        echo $this->next;
    }
?>
</ul>
