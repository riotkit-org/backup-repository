from setuptools import setup, find_packages
import yaml
import os

version = '?'

with open(os.path.dirname(os.path.realpath(__file__)) + '/../src/config/version.yaml', 'rb') as yamlFile:
    asDict = yaml.safe_load(yamlFile)
    version = asDict['version']

setup(
    name="File Repository Little Copy Machine",
    version=version,
    package_dir={'': 'src'},
    packages=find_packages(where='src'),
    entry_points={
        'console_scripts': ['riotkit.filerepository.littlecopymachine=littlecopymachine:main'],
    }, install_requires=['requests', 'sqlalchemy==1.3.13']
)
