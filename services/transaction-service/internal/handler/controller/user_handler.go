package controller

import (
	"github.com/gofiber/fiber/v2"
	"github.com/laravel2004/auth-service/internal/model/dto"
	"github.com/laravel2004/auth-service/internal/service"
)

type (
	IUserHandler interface {
		LoginUser(ctx *fiber.Ctx) error
		RegisterUser(ctx *fiber.Ctx) error
	}

	UserHandler struct {
		service service.IUserService
	}
)

func NewUserHandler(service service.IUserService) *UserHandler {
	return &UserHandler{
		service: service,
	}
}

func (h *UserHandler) LoginUser(c *fiber.Ctx) error {
	var request dto.LoginRequest
	if err := c.BodyParser(&request); err != nil {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"message": "Invalid request",
			"data":    nil,
			"error":   "Invalid request",
		})
	}

	user, err := h.service.LoginUser(request)
	if err != nil {
		return c.Status(err.StatusCode).JSON(fiber.Map{
			"message": err.Message,
			"data":    nil,
			"error":   err.Err.Error(),
		})
	}

	return c.Status(200).JSON(fiber.Map{
		"message": "Login successful",
		"data":    user,
		"error":   nil,
	})
}

func (h *UserHandler) RegisterUser(c *fiber.Ctx) error {
	var request dto.RegisterRequest
	if err := c.BodyParser(&request); err != nil {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"message": "Invalid request",
			"data":    nil,
			"error":   "Invalid request",
		})
	}

	user, err := h.service.RegisterUser(request)
	if err != nil {
		return c.Status(err.StatusCode).JSON(fiber.Map{
			"message": err.Message,
			"data":    nil,
			"error":   err.Err.Error(),
		})
	}

	return c.Status(200).JSON(fiber.Map{
		"message": "Registration successful",
		"data":    user,
		"error":   nil,
	})
}
