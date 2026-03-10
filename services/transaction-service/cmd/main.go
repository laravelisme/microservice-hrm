package main

import (
	"fmt"
	"github.com/laravel2004/auth-service/internal/config"
	route "github.com/laravel2004/auth-service/internal/router"
	"log"
	"os"
)

func main() {
	app := config.NewFiber()
	route.Init(app)
	port := os.Getenv("PORT")
	if port == "" {
		port = "3000"
	}

	fmt.Printf("Starting server on port %s\n", port)

	if err := app.Listen("0.0.0.0:" + port); err != nil {
		log.Fatalf("Failed to start server on port %s: %v\n", port, err)
	}
}
