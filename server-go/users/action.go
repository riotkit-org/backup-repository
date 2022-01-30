package users

func LookupUser(id string) (User, error) {
	return findUserById(), nil
}
