import os
from rkd import main as rkd_main, RiotKitDoApplication


def main():
    RiotKitDoApplication.load_environment()

    os.environ['RKD_PATH'] = os.path.dirname(os.path.realpath(__file__)) + '/internal:' + os.getenv('RKD_PATH', '')
    os.environ['RKD_DIST_NAME'] = 'bahub'
    os.environ['RKD_BIN'] = 'bahub'
    os.environ['RKD_UI'] = 'true'

    rkd_main()


if __name__ == '__main__':
    main()
