package users

type Permissions interface {
}

type UserPermissions struct {
}

type User struct {
	Id          string
	Email       string
	Permissions UserPermissions
}

func (u User) toJson() {

}
