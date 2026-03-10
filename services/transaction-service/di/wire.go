package di

import (
	"github.com/google/wire"
	"github.com/laravel2004/auth-service/internal/handler/controller"
	"github.com/laravel2004/auth-service/internal/repository"
	"github.com/laravel2004/auth-service/internal/service"
	"gorm.io/gorm"
)

func UserDII(db *gorm.DB) *controller.UserHandler {
	wire.Build(
		wire.NewSet(
			repository.NewUserRepository,
			service.NewUserService,
			controller.NewUserHandler,
			wire.Bind(new(service.IUserService), new(*service.UserService)),
			wire.Bind(new(repository.IUserRepository), new(*repository.UserRepository)),
			wire.Bind(new(controller.IUserHandler), new(*controller.UserHandler)),
		),
	)
	return &controller.UserHandler{}
}
