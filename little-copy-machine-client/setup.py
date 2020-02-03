from setuptools import setup, find_packages
import yaml
import os

version_yaml_path = 'unknown'
version_yaml_paths = [
    os.path.dirname(os.path.realpath(__file__)) + '/../server/config/version.yaml',
    '/build/version.yaml'
]

for path in version_yaml_paths:
    if os.path.isfile(path):
        version_yaml_path = path

with open(version_yaml_path, 'rb') as yaml_content:
    as_dict = yaml.safe_load(yaml_content)
    version = as_dict['version']

    setup(
        name="File Repository Little Copy Machine",
        version=version,
        package_dir={'': 'src'},
        packages=find_packages(where='src'),
        entry_points={
            'console_scripts': ['rkt-lcm=riotkit.filerepository.littlecopymachine:main'],
        }, install_requires=['requests>=2.20', 'sqlalchemy>=1.3']
    )
