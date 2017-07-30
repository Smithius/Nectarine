<?php

namespace Controller;

class Home
{
    /**
     * @Route
     */
    public function index()
    {
        return [];
    }

    /**
     * @Route("blog/{id}")
     * @param string $id
     * @return array
     */
    public function blog($id = null)
    {
        return [
            'id' => $id,
        ];
    }
}
