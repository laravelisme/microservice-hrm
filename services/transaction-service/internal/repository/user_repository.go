package repository

import (
	"github.com/laravel2004/auth-service/internal/model/database"
	"github.com/laravel2004/auth-service/internal/model/dto"
	"gorm.io/gorm"
)

type (
	IUserRepository interface {
		LoginUser(user dto.LoginRequest) (*database.User, error)
		RegisterUser(user dto.RegisterRequest) (*database.User, error)
		GetEmailUser(email string) (*database.User, error)
	}

	UserRepository struct {
		DB *gorm.DB
	}
)

func NewUserRepository(db *gorm.DB) *UserRepository {
	return &UserRepository{DB: db}
}

func (r *UserRepository) GetEmailUser(email string) (*database.User, error) {
	var userModel database.User
	if err := r.DB.Where("email = ?", email).First(&userModel).Error; err != nil {
		return nil, err
	}
	return &userModel, nil
}

func (r *UserRepository) LoginUser(user dto.LoginRequest) (*database.User, error) {
	var userModel database.User
	if err := r.DB.Where("username = ?", user.Username).First(&userModel).Error; err != nil {
		return nil, err
	}
	return &userModel, nil

}

func (r *UserRepository) RegisterUser(user dto.RegisterRequest) (*database.User, error) {
	var userModel database.User
	userModel.Username = user.Username
	userModel.Email = user.Email
	userModel.Password = user.Password

	if err := r.DB.Create(&userModel).Error; err != nil {
		return nil, err
	}
	return &userModel, nil
}
