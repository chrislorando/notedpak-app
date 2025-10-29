<?php

use Laravel\Mcp\Facades\Mcp;

// Local MCP server for CLI development
Mcp::local('todo', \App\Mcp\Servers\TodoServer::class);

// HTTP MCP server with Sanctum authentication
// Note: Use Bearer token in Authorization header instead of OAuth flow
Mcp::web('/mcp/todo', \App\Mcp\Servers\TodoServer::class)->middleware(['auth:sanctum']);