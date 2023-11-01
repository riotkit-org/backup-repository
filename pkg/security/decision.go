package security

import "k8s.io/utils/strings/slices"

type DecisionRequest struct {
	Actor   Actor
	Subject Subject
	Action  string
}

// DecideCanDo is taking a decision if specific Action can be made on by Actor on a Subject
//
// Logic:
//
//  1. Subject defines who can access it and how
//
//  2. There are SYSTEM-WIDE roles defined on the user that allows globally do everything
//
//  3. User can generate a LIMITED SCOPE JWT token with /auth/login endpoint. This kind of token can
//     define that in context of given Subject the roles should be limited to specific ones
//     Important! Those roles cannot be higher than defined on the Subject or on the Actor in its profile
//
// Cases:
//
//	Has limited token: User generates JWT with "backupDownloader" role in context of "iwa-ait" collection.
//	                   So even if that User is a "collectionManager" for this collection, with that specific JWT token
//	                   its possible to only download backups.
func DecideCanDo(dr *DecisionRequest) bool {
	// CASE: Decision about global action, not in context of a collection
	//       For example - to see a system health check endpoint
	if dr.Subject == nil {
		return CanThoseRolesPerformAction(dr.Actor.GetRoles(), dr.Action)
	}

	// CASE: If we are in a context of an Access Key, then it has its own limited scope
	if dr.Actor.IsInAccessKeyContext() {
		scopedRoles := dr.Actor.GetAccessKeyRolesInContextOf(dr.Subject)
		if !CanThoseRolesPerformAction(scopedRoles, dr.Action) {
			return false
		}
	}

	if hasCurrentTokenLimitations(dr.Actor) {
		limitations := dr.Actor.GetSessionLimitedOperationsScope()
		foundAllowing := false

		for _, object := range limitations.Elements {
			if object.Type == dr.Subject.GetTypeName() && object.Name == dr.Subject.GetId() {
				foundAllowing = CanThoseRolesPerformAction(object.Roles, dr.Action)
			}
		}

		// CASE: User has a limited token generated, and no any entry in `operationsScope` field is
		//       matching Subject for given Action
		if !foundAllowing {
			return false
		}
	}

	// CASE: User is explicitly listed in object's ACL, that it owns this object
	objectSpecificDecision := dr.Subject.GetAccessControlList().IsPermitted(dr.Actor.GetName(), dr.Actor.GetTypeName(), dr.Action)

	// CASE: e.g. is a system administrator
	systemWideRoleDecision := CanThoseRolesPerformAction(dr.Actor.GetRoles(), dr.Action)

	return objectSpecificDecision || systemWideRoleDecision
}

// CanThoseRolesPerformAction checks if any listed role is allowing to perform action
func CanThoseRolesPerformAction(roles []string, action string) bool {
	return slices.Contains(expandActions(expandRoles(roles)), action)
}

func expandRoles(roles []string) []string {
	inheritance := GetRolesInheritance()
	expanded := make([]string, 0)
	expanded = append(expanded, roles...)

	// level: 0
	for _, role := range roles {
		children, expandable := inheritance[role]

		if expandable {
			for _, element := range children {
				if !slices.Contains(roles, element) {
					expanded = append(expanded, element)
				}
				expanded = append(expanded, expandRoles([]string{element})...)
			}
		}
	}
	return expanded
}

func expandActions(roles []string) []string {
	mapping := GetRolesActions()
	actions := make([]string, 0)
	for _, role := range roles {
		if roleActions, exists := mapping[role]; exists {
			actions = append(actions, roleActions...)
		}
	}
	return actions
}

func hasCurrentTokenLimitations(a Actor) bool {
	if a.GetSessionLimitedOperationsScope() == nil || a.GetSessionLimitedOperationsScope().Elements == nil {
		return false
	}
	return len(a.GetSessionLimitedOperationsScope().Elements) > 0
}

type Actor interface {
	IsInAccessKeyContext() bool
	GetAccessKeyRolesInContextOf(Subject) Roles
	GetRoles() Roles
	GetEmail() string
	GetName() string
	GetTypeName() string
	GetSessionLimitedOperationsScope() *SessionLimitedOperationsScope
}

type Subject interface {
	GetId() string
	GetTypeName() string
	GetAccessControlList() *AccessControlList
}
