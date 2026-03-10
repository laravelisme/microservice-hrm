package v1

import (
	"github.com/gofiber/fiber/v2"
	"github.com/laravel2004/auth-service/di"
	"github.com/laravel2004/auth-service/internal/config"
)

func InitUserRoute(c fiber.Router) {
	r := di.UserDI(config.GetDB())
	c.Post("/register", r.RegisterUser)
	c.Post("/login", r.LoginUser)
}
