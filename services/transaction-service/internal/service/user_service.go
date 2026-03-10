package service

import (
	"errors"
	"github.com/laravel2004/auth-service/internal/common"
	"github.com/laravel2004/auth-service/internal/model/dto"
	"github.com/laravel2004/auth-service/internal/repository"
	"golang.org/x/crypto/bcrypt"
)

type (
	IUserService interface {
		LoginUser(user dto.LoginRequest) (*dto.LoginResponse, *common.ServiceErrorDto)
		RegisterUser(user dto.RegisterRequest) (*dto.UserResponse, *common.ServiceErrorDto)
	}

	UserService struct {
		repo repository.IUserRepository
	}
)

func NewUserService(repo repository.IUserRepository) *UserService {
	return &UserService{repo: repo}
}

func (s *UserService) LoginUser(user dto.LoginRequest) (*dto.LoginResponse, *common.ServiceErrorDto) {
	result, err := s.repo.LoginUser(user)
	if err != nil {
		return nil, common.InternalServiceError(err)
	}

	if err := bcrypt.CompareHashAndPassword([]byte(result.Password), []byte(user.Password)); err != nil {
		return nil, common.InternalServiceError(err)
	}

	token, err := common.GenerateJWT(result.ID)
	if err != nil {
		return nil, common.InternalServiceError(err)
	}

	return &dto.LoginResponse{
		Token: token,
		User: dto.UserResponse{
			ID:       uint(result.ID),
			Username: result.Username,
			Email:    result.Email,
		},
	}, nil
}

func (s *UserService) RegisterUser(user dto.RegisterRequest) (*dto.UserResponse, *common.ServiceErrorDto) {
	hashedPassword, err := bcrypt.GenerateFromPassword([]byte(user.Password), bcrypt.DefaultCost)
	if err != nil {
		return nil, common.InternalServiceError(err)
	}
	user.Password = string(hashedPassword)

	existingUser, err := s.repo.GetEmailUser(user.Email)
	if err != nil && err.Error() != "record not found" {
		return nil, common.InternalServiceError(err)
	}
	if existingUser != nil {
		return nil, &common.ServiceErrorDto{
			Message:    "Email sudah terdaftar",
			StatusCode: 400,
			Err:        errors.New("duplicate email"),
		}
	}

	result, err := s.repo.RegisterUser(user)
	if err != nil {
		return nil, common.InternalServiceError(err)
	}

	return &dto.UserResponse{
		ID:       uint(result.ID),
		Username: result.Username,
		Email:    result.Email,
	}, nil
}
