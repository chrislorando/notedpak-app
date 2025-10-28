<?php

use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
Mcp::local('todo', \App\Mcp\Servers\TodoServer::class);
Mcp::web('/mcp/todo', \App\Mcp\Servers\TodoServer::class)->middleware(['auth:sanctum']);