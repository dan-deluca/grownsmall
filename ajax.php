<?php
    if ( $_POST['shoppingCart'] ) {
        $shoppingCart = array();
        
        foreach ( $_POST['shoppingCart'] as $item ) {
            if (is_array($item)) {
                $shoppingCart[] = $item;
            }
        }
        
        // this is your array values => $shoppingCart
        
        echo "<h2>Your php array</h2>";
        echo '<pre>'; print_r( $shoppingCart ); echo '</pre>';
    }
?>