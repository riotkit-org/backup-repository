import React from 'react'
// import PropTypes from 'prop-types'

// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";

import Button from "components/CustomButtons/Button.js";
import GridItem from "components/Grid/GridItem.js";
import GridContainer from "components/Grid/GridContainer.js";
import Card from "components/Card/Card.js";
import CardHeader from "components/Card/CardHeader.js";
import CardBody from "components/Card/CardBody.js";
import CardFooter from "components/Card/CardFooter.js";

import CustomInput from "components/CustomInput/CustomInput.js";

import Code from "@material-ui/icons/Code";
import Tasks from "components/Tasks/Tasks.js";
import CustomTabs from "components/CustomTabs/CustomTabs.js";

import { website, } from "variables/general.js";

const styles = {
    cardCategoryWhite: {
      color: "rgba(255,255,255,.62)",
      margin: "0",
      fontSize: "14px",
      marginTop: "0",
      marginBottom: "0"
    },
    cardTitleWhite: {
      color: "#FFFFFF",
      marginTop: "0px",
      minHeight: "auto",
      fontWeight: "300",
      fontFamily: "'Roboto', 'Helvetica', 'Arial', sans-serif",
      marginBottom: "3px",
      textDecoration: "none"
    }
  };

const useStyles = makeStyles(styles);



const CreateToken = props => {

const classes = useStyles();


    return (
        <GridItem xs={12} sm={12} md={12}>
          <Card>
            <CardHeader color="primary">
              <h4 className={classes.cardTitleWhite}>Create New Token</h4>
              <p className={classes.cardCategoryWhite}>Complete token:</p>
            </CardHeader>
            <CardBody>
              <GridContainer>
                <GridItem xs={12} sm={12} md={6}>
                  <CustomInput
                    labelText="Token Name"
                    id="first-name"
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
                <GridItem xs={12} sm={12} md={6}>
                  <CustomInput
                    labelText="Last Name"
                    id="last-name"
                    formControlProps={{
                      fullWidth: true
                    }}
                  />
                </GridItem>
              </GridContainer>

              <div className={classes.typo}>
                <h3>Permitions:</h3>
              </div>
              <CustomTabs
                title="Tasks:"
                headerColor="primary"
                tabs={[
                  {
                    tabName: "Permitions",
                    tabIcon: Code,
                    tabContent: (
                      <Tasks
                        checkedIndexes={[0]}
                        tasksIndexes={[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]}
                        tasks={Array.from(props.displayedPermitions)}
                      />
                    )
                  },
                  {
                    tabName: "Website",
                    tabIcon: Code,
                    tabContent: (
                      <Tasks
                        checkedIndexes={[0]}
                        tasksIndexes={[0, 1]}
                        tasks={website}
                      />
                    )
                  },
                ]}
              />
            </CardBody>

            <CardFooter>
              <Button color="primary">Update Token</Button>
            </CardFooter>
          </Card>
        </GridItem>
    )
}

CreateToken.propTypes = {

}

export default CreateToken
