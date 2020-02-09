/*eslint-disable*/
import React from "react";
import PropTypes from "prop-types";
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";
import ListItem from "@material-ui/core/ListItem";
import List from "@material-ui/core/List";
// core components
import styles from "assets/jss/material-dashboard-react/components/footerStyle.js";

const useStyles = makeStyles(styles);

export default function Footer(props) {
  const classes = useStyles();
  return (
    <footer className={classes.footer}>
      <div className={classes.container}>
        <div className={classes.left}>
          <List className={classes.list}>
            <ListItem className={classes.inlineBlock}>
              <a
                href="https://github.com/riotkit-org/file-repository"
                className={classes.block}
              >
                Repository
              </a>
            </ListItem>
            <ListItem className={classes.inlineBlock}>
              <a
                href="https://file-repository.readthedocs.io/en/latest/index.html"
                className={classes.block}
              >
                Documentation
              </a>
            </ListItem>
            <ListItem className={classes.inlineBlock}>
              <a
                href="https://test-file-repository.riotkit.org/api/doc"
                className={classes.block}
              >
                API/SWAGGER
              </a>
            </ListItem>
            <ListItem className={classes.inlineBlock}>
              <a href="https://www.facebook.com/riotkit.org" className={classes.block}>
                Facebook
              </a>
            </ListItem>
          </List>
        </div>
        <p className={classes.right}>
          <span>
            &copy; {1900 + new Date().getYear()}{" "}
            <a
              href="https://github.com/riotkit-org"
              target="_blank"
              className={classes.a}
            >
              RiotKit
            </a>
            , made with love for a better web
          </span>
        </p>
      </div>
    </footer>
  );
}
