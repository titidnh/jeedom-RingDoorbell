PROGRESS_FILE=/tmp/dependancy_RingDoorbell_in_progress
if [ ! -z $1 ]; then
        PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
apt-get update
echo 30 > ${PROGRESS_FILE}
apt-get install -y python3-pip python3
echo 60 > ${PROGRESS_FILE}
pip3 install --upgrade pip
echo 80 > ${PROGRESS_FILE}
pip3 install --upgrade ring_doorbell
echo 100 > ${PROGRESS_FILE}
rm ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"