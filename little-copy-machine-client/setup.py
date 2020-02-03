from setuptools import setup, find_packages
import yaml
import os

version = '?'

with open(os.path.dirname(os.path.realpath(__file__)) + '/../server/config/version.yaml', 'rb') as yamlFile:
    asDict = yaml.safe_load(yamlFile)
    version = asDict['version']

setup(
    name="File Repository Little Copy Machine",
    version=version,
    package_dir={'': 'src'},
    packages=find_packages(where='src'),
    entry_points={
        'console_scripts': ['rkt-lcm=riotkit.filerepository.littlecopymachine:main'],
    }, install_requires=['requests>=2.20', 'sqlalchemy>=1.3']
)
