#!/usr/bin/env python3

from setuptools import setup, find_packages

setup(
    setup_requires=['pbr'],
    pbr=True,
    package_dir={'': './'},
    packages=find_packages(where='./'),
    package_data={
        'bahub': [
            'internal/*',
            'internal/**/*',
            'internal/schema/*'
        ]
    }
)
