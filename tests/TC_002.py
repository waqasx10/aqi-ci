import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
import time

def test_csv_missing():
    driver = webdriver.Chrome()
    driver.get("http://127.0.0.1:8000/")
    
    # upload invalid file
    file_input = driver.find_element(By.ID, "file-input")
    file_input.send_keys("/Users/waqas/Desktop/csv2/cont.csv")
    time.sleep(5)

    # click Process
    process_btn = driver.find_element(By.XPATH, "//button[normalize-space()='Process']")
    process_btn.click()
    time.sleep(2)
    
    
    error_msg = driver.find_element(By.XPATH, "//div[@id='file-error']")
    assert "Could not read the file" in error_msg.text

    