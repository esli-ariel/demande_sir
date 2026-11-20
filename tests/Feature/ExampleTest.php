<?php

it('redirects guests from the home page', function () {
    $this->get('/')->assertRedirect();
});
